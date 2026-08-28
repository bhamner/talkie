package kids.talkie;

import android.content.res.Configuration;
import android.os.Build;
import android.os.Bundle;
import android.webkit.ServiceWorkerClient;
import android.webkit.ServiceWorkerController;
import android.webkit.WebResourceRequest;
import android.webkit.WebResourceResponse;
import androidx.core.view.WindowCompat;
import com.getcapacitor.Bridge;
import com.getcapacitor.BridgeActivity;

public class MainActivity extends BridgeActivity {
    @Override
    public void setContentView(int layoutResID) {
        if (layoutResID == com.getcapacitor.android.R.layout.capacitor_bridge_layout_main) {
            super.setContentView(R.layout.activity_main);
            return;
        }

        super.setContentView(layoutResID);
    }

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        WindowCompat.setDecorFitsSystemWindows(getWindow(), true);

        Bridge bridge = getBridge();
        if (bridge == null) {
            return;
        }

        applyShellInsets();

        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.N) {
            ServiceWorkerController.getInstance()
                .setServiceWorkerClient(
                    new ServiceWorkerClient() {
                        @Override
                        public WebResourceResponse shouldInterceptRequest(WebResourceRequest request) {
                            if (LiveSiteFetcher.shouldProxy(request.getMethod(), request.getUrl().getHost())) {
                                WebResourceResponse proxied = LiveSiteFetcher.fetch(request);
                                if (proxied != null) {
                                    return proxied;
                                }
                            }

                            if (LiveSiteRequests.isLiveTalkieHost(request.getUrl().getHost())) {
                                return null;
                            }

                            return bridge.getLocalServer().shouldInterceptRequest(request);
                        }
                    }
                );
        }
    }

    @Override
    public void onConfigurationChanged(Configuration newConfig) {
        super.onConfigurationChanged(newConfig);
        WindowCompat.setDecorFitsSystemWindows(getWindow(), true);
        applyShellInsets();
    }

    private void applyShellInsets() {
        TalkieWebView.applySystemBarPadding(findViewById(R.id.talkie_root));
    }
}
