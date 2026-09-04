package kids.talkie;

import static org.junit.Assert.assertTrue;

import org.junit.Test;

public class DeviceTtsScriptTest {
    @Test
    public void patchesBrowserSpeechThroughNativeBridge() {
        String script = DeviceTtsScript.PATCH;

        assertTrue(script.contains("window.TalkieDeviceTts"));
        assertTrue(script.contains("window.__talkieDeviceTts"));
        assertTrue(script.contains("window.speechSynthesis.speak=speak"));
        assertTrue(script.contains("window.speechSynthesis.cancel=cancel"));
        assertTrue(script.contains("bridge.speak"));
        assertTrue(script.contains("bridge.cancel"));
    }
}
