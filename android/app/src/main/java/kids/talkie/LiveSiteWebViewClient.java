package kids.talkie;

import android.graphics.Bitmap;
import android.webkit.RenderProcessGoneDetail;
import android.webkit.WebResourceError;
import android.webkit.WebResourceRequest;
import android.webkit.WebResourceResponse;
import android.webkit.WebView;
import android.webkit.WebViewClient;

final class LiveSiteWebViewClient extends WebViewClient {
    private final WebViewClient inner;

    LiveSiteWebViewClient(WebViewClient inner) {
        this.inner = inner;
    }

    @Override
    public WebResourceResponse shouldInterceptRequest(WebView view, WebResourceRequest request) {
        if (LiveSiteFetcher.shouldProxy(request.getMethod(), request.getUrl().getHost())) {
            WebResourceResponse proxied = LiveSiteFetcher.fetch(request);
            if (proxied != null) {
                return proxied;
            }
        }

        if (LiveSiteRequests.isLiveTalkieHost(request.getUrl().getHost())) {
            return null;
        }

        return inner.shouldInterceptRequest(view, request);
    }

    @Override
    public boolean shouldOverrideUrlLoading(WebView view, WebResourceRequest request) {
        return inner.shouldOverrideUrlLoading(view, request);
    }

    @Deprecated
    @Override
    public boolean shouldOverrideUrlLoading(WebView view, String url) {
        return inner.shouldOverrideUrlLoading(view, url);
    }

    @Override
    public void onPageStarted(WebView view, String url, Bitmap favicon) {
        inner.onPageStarted(view, url, favicon);
    }

    @Override
    public void onPageFinished(WebView view, String url) {
        inner.onPageFinished(view, url);
    }

    @Override
    public void onReceivedError(WebView view, WebResourceRequest request, WebResourceError error) {
        inner.onReceivedError(view, request, error);
    }

    @Override
    public void onReceivedHttpError(WebView view, WebResourceRequest request, WebResourceResponse errorResponse) {
        inner.onReceivedHttpError(view, request, errorResponse);
    }

    @Override
    public boolean onRenderProcessGone(WebView view, RenderProcessGoneDetail detail) {
        return inner.onRenderProcessGone(view, detail);
    }
}
