package kids.talkie;

import android.content.Context;
import android.media.AudioAttributes;
import android.os.Handler;
import android.os.Looper;
import android.speech.tts.TextToSpeech;
import android.util.Log;
import java.util.Locale;

final class DeviceTts {
    private static final String TAG = "TalkieDeviceTts";
    private static final int MAX_CHARS = 4000;

    private final Handler main = new Handler(Looper.getMainLooper());
    private final TextToSpeech tts;
    private boolean ready = false;
    private String pending;

    DeviceTts(Context context) {
        tts = new TextToSpeech(context.getApplicationContext(), this::onInit);
    }

    void speak(String text) {
        String spoken = sanitize(text);
        if (spoken.isEmpty()) {
            return;
        }

        main.post(() -> {
            if (!ready) {
                pending = spoken;
                return;
            }

            int status = tts.speak(spoken, TextToSpeech.QUEUE_FLUSH, null, "talkie-device");
            if (status == TextToSpeech.ERROR) {
                Log.e(TAG, "Android TTS rejected speech");
            }
        });
    }

    void cancel() {
        main.post(() -> {
            pending = null;
            if (ready) {
                tts.stop();
            }
        });
    }

    void shutdown() {
        main.post(() -> {
            pending = null;
            ready = false;
            tts.stop();
            tts.shutdown();
        });
    }

    private void onInit(int status) {
        if (status != TextToSpeech.SUCCESS) {
            Log.e(TAG, "Android TTS failed to initialize: " + status);
            return;
        }

        tts.setAudioAttributes(
            new AudioAttributes.Builder()
                .setUsage(AudioAttributes.USAGE_MEDIA)
                .setContentType(AudioAttributes.CONTENT_TYPE_SPEECH)
                .build()
        );
        tts.setLanguage(Locale.getDefault());
        ready = true;

        if (pending != null) {
            String queued = pending;
            pending = null;
            speak(queued);
        }
    }

    private static String sanitize(String text) {
        if (text == null) {
            return "";
        }

        String trimmed = text.trim();
        if (trimmed.length() <= MAX_CHARS) {
            return trimmed;
        }

        return trimmed.substring(0, MAX_CHARS);
    }
}
