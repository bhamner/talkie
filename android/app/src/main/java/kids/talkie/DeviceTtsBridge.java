package kids.talkie;

import android.webkit.JavascriptInterface;

final class DeviceTtsBridge {
    static final String NAME = "TalkieDeviceTts";

    private final DeviceTts tts;

    DeviceTtsBridge(DeviceTts tts) {
        this.tts = tts;
    }

    @JavascriptInterface
    public void speak(String text) {
        tts.speak(text);
    }

    @JavascriptInterface
    public void cancel() {
        tts.cancel();
    }
}
