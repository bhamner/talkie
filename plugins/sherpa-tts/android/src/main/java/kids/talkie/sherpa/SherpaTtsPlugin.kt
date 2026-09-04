package kids.talkie.sherpa

import android.media.AudioAttributes
import android.media.AudioFormat
import android.media.AudioManager
import android.media.AudioTrack
import android.util.Log
import com.getcapacitor.JSObject
import com.getcapacitor.Plugin
import com.getcapacitor.PluginCall
import com.getcapacitor.PluginMethod
import com.getcapacitor.annotation.CapacitorPlugin
import com.k2fsa.sherpa.onnx.OfflineTts
import com.k2fsa.sherpa.onnx.OfflineTtsConfig
import com.k2fsa.sherpa.onnx.OfflineTtsModelConfig
import com.k2fsa.sherpa.onnx.OfflineTtsVitsModelConfig
import org.apache.commons.compress.archivers.tar.TarArchiveEntry
import org.apache.commons.compress.archivers.tar.TarArchiveInputStream
import org.apache.commons.compress.compressors.bzip2.BZip2CompressorInputStream
import java.io.File
import java.io.FileOutputStream
import java.net.HttpURLConnection
import java.net.URL
import java.util.concurrent.Executors

@CapacitorPlugin(name = "SherpaTts")
class SherpaTtsPlugin : Plugin() {
    private val executor = Executors.newSingleThreadExecutor()
    private val ttsLock = Any()

    @Volatile
    private var cancelled = false

    @Volatile
    private var stopPlayback = false

    @Volatile
    private var track: AudioTrack? = null

    private var tts: OfflineTts? = null
    private var loadedModel: String? = null

    @PluginMethod
    fun warmup(call: PluginCall) {
        val model = call.getString("model")
        if (model.isNullOrBlank()) {
            call.reject("Missing model")
            return
        }

        executor.execute {
            try {
                cancelled = false
                ensureReady(model)
                emitProgress("idle", 0, 0)
                call.resolve()
            } catch (error: Exception) {
                Log.e(TAG, "Warmup failed", error)
                emitProgress("idle", 0, 0)
                call.reject(error.message ?: "Sherpa warmup failed")
            }
        }
    }

    @PluginMethod
    fun speak(call: PluginCall) {
        val text = call.getString("text")?.trim()
        val model = call.getString("model")
        val speakerId = call.getInt("speakerId") ?: 0

        if (text.isNullOrEmpty() || model.isNullOrBlank()) {
            call.reject("Missing text or model")
            return
        }

        executor.execute {
            try {
                stopPlayback = false
                cancelled = false
                val engine = ensureReady(model)
                emitProgress("idle", 0, 0)
                play(engine, text, speakerId)
                call.resolve()
            } catch (error: Exception) {
                Log.e(TAG, "Speak failed", error)
                emitProgress("idle", 0, 0)
                call.reject(error.message ?: "Sherpa speak failed")
            }
        }
    }

    @PluginMethod
    fun cancel(call: PluginCall) {
        stopPlayback = true
        stopTrack()
        call.resolve()
    }

    private fun ensureReady(model: String): OfflineTts {
        val spec = MODELS[model] ?: throw IllegalArgumentException("Unsupported Piper model: $model")
        val modelDir = prepareModel(spec)

        synchronized(ttsLock) {
            val current = tts
            if (current != null && loadedModel == model) {
                return current
            }

            current?.release()
            emitProgress("preparing", 0, 0)

            val config = OfflineTtsConfig(
                model = OfflineTtsModelConfig(
                    vits = OfflineTtsVitsModelConfig(
                        model = File(modelDir, spec.onnxName).absolutePath,
                        tokens = File(modelDir, "tokens.txt").absolutePath,
                        dataDir = File(modelDir, "espeak-ng-data").absolutePath,
                    ),
                    numThreads = 2,
                    debug = false,
                    provider = "cpu",
                ),
            )

            val created = OfflineTts(assetManager = null, config = config)
            tts = created
            loadedModel = model
            return created
        }
    }

    private fun prepareModel(spec: ModelSpec): File {
        val root = File(context.filesDir, "piper/${spec.id}")
        val modelDir = File(root, spec.extractDir)
        val ready = File(root, ".ready")

        if (ready.exists() && File(modelDir, spec.onnxName).isFile && File(modelDir, "tokens.txt").isFile) {
            return modelDir
        }

        root.mkdirs()
        val archive = File(root, spec.archiveName)
        download(spec.url, archive)
        emitProgress("preparing", 0, 0)
        extractTarBz2(archive, root)
        archive.delete()

        if (!File(modelDir, spec.onnxName).isFile || !File(modelDir, "tokens.txt").isFile) {
            throw IllegalStateException("Piper model files were missing after download")
        }

        ready.writeText(spec.id)
        return modelDir
    }

    private fun download(url: String, dest: File) {
        emitProgress("downloading", 0, 0)
        val connection = (URL(url).openConnection() as HttpURLConnection).apply {
            connectTimeout = 30_000
            readTimeout = 120_000
            instanceFollowRedirects = true
        }

        try {
            if (connection.responseCode !in 200..299) {
                throw IllegalStateException("Download failed (${connection.responseCode})")
            }

            val total = connection.contentLengthLong.coerceAtLeast(0)
            dest.parentFile?.mkdirs()
            connection.inputStream.use { input ->
                FileOutputStream(dest).use { output ->
                    val buffer = ByteArray(64 * 1024)
                    var loaded = 0L
                    var lastEmit = 0L
                    while (true) {
                        val read = input.read(buffer)
                        if (read <= 0) {
                            break
                        }
                        output.write(buffer, 0, read)
                        loaded += read
                        if (cancelled) {
                            throw InterruptedException("Download cancelled")
                        }
                        if (loaded - lastEmit >= 512 * 1024 || loaded == total) {
                            emitProgress("downloading", loaded, total)
                            lastEmit = loaded
                        }
                    }
                }
            }
            emitProgress("downloading", dest.length(), total)
        } finally {
            connection.disconnect()
        }
    }

    private fun extractTarBz2(archive: File, dest: File) {
        val destPath = dest.canonicalFile
        archive.inputStream().use { fileStream ->
            BZip2CompressorInputStream(fileStream).use { bzip ->
                TarArchiveInputStream(bzip).use { tar ->
                    var entry: TarArchiveEntry? = tar.nextEntry
                    while (entry != null) {
                        val outFile = File(dest, entry.name).canonicalFile
                        if (!outFile.path.startsWith(destPath.path + File.separator) && outFile != destPath) {
                            throw IllegalStateException("Blocked unsafe archive path: ${entry.name}")
                        }

                        if (entry.isDirectory) {
                            outFile.mkdirs()
                        } else {
                            outFile.parentFile?.mkdirs()
                            FileOutputStream(outFile).use { output ->
                                tar.copyTo(output)
                            }
                        }
                        entry = tar.nextEntry
                    }
                }
            }
        }
    }

    private fun play(engine: OfflineTts, text: String, speakerId: Int) {
        val generated = engine.generate(text, speakerId, 1.0f)
        val samples = generated.samples
        if (samples.isEmpty()) {
            throw IllegalStateException("Piper generated no audio")
        }

        val pcm = Pcm16.fromFloatSamples(samples)
        val sampleRate = generated.sampleRate
        val minBuffer = AudioTrack.getMinBufferSize(
            sampleRate,
            AudioFormat.CHANNEL_OUT_MONO,
            AudioFormat.ENCODING_PCM_16BIT,
        )
        if (minBuffer <= 0) {
            throw IllegalStateException("AudioTrack rejected sample rate $sampleRate")
        }

        val bufferSize = minBuffer
        val audioTrack = AudioTrack(
            AudioAttributes.Builder()
                .setUsage(AudioAttributes.USAGE_MEDIA)
                .setContentType(AudioAttributes.CONTENT_TYPE_SPEECH)
                .build(),
            AudioFormat.Builder()
                .setEncoding(AudioFormat.ENCODING_PCM_16BIT)
                .setSampleRate(sampleRate)
                .setChannelMask(AudioFormat.CHANNEL_OUT_MONO)
                .build(),
            bufferSize,
            AudioTrack.MODE_STREAM,
            AudioManager.AUDIO_SESSION_ID_GENERATE,
        )

        track = audioTrack
        audioTrack.play()

        try {
            var offset = 0
            while (offset < pcm.size && !stopPlayback) {
                val written = audioTrack.write(pcm, offset, pcm.size - offset)
                if (written <= 0) {
                    throw IllegalStateException("AudioTrack write failed ($written)")
                }
                offset += written
            }

            if (!stopPlayback) {
                drain(audioTrack, pcm.size, sampleRate)
            }
        } finally {
            if (track === audioTrack) {
                stopTrack()
            } else {
                audioTrack.release()
            }
        }
    }

    private fun drain(audioTrack: AudioTrack, frames: Int, sampleRate: Int) {
        val timeoutMs = (frames * 1000L / sampleRate.coerceAtLeast(1)) + 250L
        val started = System.currentTimeMillis()
        while (!stopPlayback && audioTrack.playState == AudioTrack.PLAYSTATE_PLAYING) {
            if (audioTrack.playbackHeadPosition >= frames - 1) {
                break
            }
            if (System.currentTimeMillis() - started > timeoutMs) {
                break
            }
            Thread.sleep(20)
        }
    }

    private fun stopTrack() {
        val current = track
        track = null
        if (current == null) {
            return
        }

        try {
            if (stopPlayback) {
                current.pause()
                current.flush()
            }
            current.stop()
        } catch (_: IllegalStateException) {
            // Already released or not initialized.
        } finally {
            current.release()
        }
    }

    private fun emitProgress(phase: String, loaded: Long, total: Long) {
        val data = JSObject()
        data.put("phase", phase)
        data.put("loaded", loaded)
        data.put("total", total)
        notifyListeners("progress", data)
    }

    private data class ModelSpec(
        val id: String,
        val url: String,
        val archiveName: String,
        val extractDir: String,
        val onnxName: String,
    )

    companion object {
        private const val TAG = "SherpaTts"

        private val MODELS = mapOf(
            "en_US-libritts_r-medium" to ModelSpec(
                id = "en_US-libritts_r-medium",
                url = "https://github.com/k2-fsa/sherpa-onnx/releases/download/tts-models/vits-piper-en_US-libritts_r-medium.tar.bz2",
                archiveName = "vits-piper-en_US-libritts_r-medium.tar.bz2",
                extractDir = "vits-piper-en_US-libritts_r-medium",
                onnxName = "en_US-libritts_r-medium.onnx",
            ),
        )
    }
}
