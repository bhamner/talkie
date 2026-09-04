package kids.talkie.sherpa

internal object Pcm16 {
    fun fromFloatSamples(samples: FloatArray): ShortArray {
        val pcm = ShortArray(samples.size)
        for (index in samples.indices) {
            val clipped = (samples[index] * 32767f).coerceIn(-32768f, 32767f)
            pcm[index] = clipped.toInt().toShort()
        }
        return pcm
    }
}
