package kids.talkie;

import android.app.Activity;
import android.content.Context;
import android.util.AttributeSet;
import android.view.View;
import android.webkit.WebViewClient;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;
import com.getcapacitor.CapacitorWebView;

public class TalkieWebView extends CapacitorWebView {
    private final DeviceTts deviceTts;

    public TalkieWebView(Context context, AttributeSet attrs) {
        super(context, attrs);
        deviceTts = new DeviceTts(context);
        addJavascriptInterface(new DeviceTtsBridge(deviceTts), DeviceTtsBridge.NAME);
    }

    @Override
    protected void onDetachedFromWindow() {
        if (getContext() instanceof Activity activity && activity.isFinishing()) {
            deviceTts.shutdown();
        }
        super.onDetachedFromWindow();
    }

    @Override
    public void setWebViewClient(WebViewClient client) {
        if (client instanceof LiveSiteWebViewClient) {
            super.setWebViewClient(client);
            return;
        }

        super.setWebViewClient(new LiveSiteWebViewClient(client));
    }

    static void applySystemBarPadding(View root) {
        if (root == null) {
            return;
        }

        ViewCompat.setOnApplyWindowInsetsListener(root, (view, windowInsets) -> {
            Insets insets = windowInsets.getInsets(WindowInsetsCompat.Type.systemBars() | WindowInsetsCompat.Type.displayCutout());
            view.setPadding(insets.left, insets.top, insets.right, insets.bottom);
            return WindowInsetsCompat.CONSUMED;
        });
        ViewCompat.requestApplyInsets(root);
    }
}
