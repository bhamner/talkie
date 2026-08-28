package kids.talkie;

import android.content.Context;
import android.util.AttributeSet;
import android.view.View;
import android.webkit.WebViewClient;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;
import com.getcapacitor.CapacitorWebView;

public class TalkieWebView extends CapacitorWebView {
    public TalkieWebView(Context context, AttributeSet attrs) {
        super(context, attrs);
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
