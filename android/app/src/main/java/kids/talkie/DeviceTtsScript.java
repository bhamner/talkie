package kids.talkie;

import android.webkit.WebView;

final class DeviceTtsScript {
    static final String PATCH =
        "(function(){" +
        "if(window.__talkieDeviceTts){return;}" +
        "var bridge=window." + DeviceTtsBridge.NAME + ";" +
        "if(!bridge){return;}" +
        "window.__talkieDeviceTts=true;" +
        "var speak=function(utterance){" +
        "bridge.speak(utterance&&utterance.text?String(utterance.text):'');" +
        "};" +
        "var cancel=function(){bridge.cancel();};" +
        "if(!window.speechSynthesis){" +
        "window.speechSynthesis={" +
        "pending:false,speaking:false,paused:false,onvoiceschanged:null," +
        "getVoices:function(){return [];}," +
        "speak:speak,cancel:cancel,pause:function(){},resume:function(){}," +
        "addEventListener:function(){},removeEventListener:function(){}" +
        "};" +
        "return;" +
        "}" +
        "window.speechSynthesis.speak=speak;" +
        "window.speechSynthesis.cancel=cancel;" +
        "})();";

    private DeviceTtsScript() {}

    static void install(WebView view) {
        if (view == null) {
            return;
        }

        view.evaluateJavascript(PATCH, null);
    }
}
