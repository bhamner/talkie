package kids.talkie;

import android.util.Log;
import android.webkit.CookieManager;
import android.webkit.WebResourceRequest;
import android.webkit.WebResourceResponse;
import java.io.ByteArrayInputStream;
import java.io.IOException;
import java.net.InetAddress;
import java.net.UnknownHostException;
import java.util.HashMap;
import java.util.List;
import java.util.Locale;
import java.util.Map;
import java.util.concurrent.TimeUnit;
import okhttp3.Dns;
import okhttp3.OkHttpClient;
import okhttp3.Request;
import okhttp3.Response;
import okhttp3.ResponseBody;
import org.json.JSONException;

final class LiveSiteFetcher {
    private static final String TAG = "TalkieLiveSite";

    private static final OkHttpClient DOH_CLIENT = new OkHttpClient.Builder()
        .dns(hostname -> {
            if ("dns.google".equalsIgnoreCase(hostname)) {
                return LiveSiteDns.googleAnycast();
            }

            throw new UnknownHostException(hostname);
        })
        .connectTimeout(15, TimeUnit.SECONDS)
        .readTimeout(20, TimeUnit.SECONDS)
        .build();

    private static final OkHttpClient SITE_CLIENT = new OkHttpClient.Builder()
        .dns(new CachedDohDns())
        .connectTimeout(15, TimeUnit.SECONDS)
        .readTimeout(30, TimeUnit.SECONDS)
        .followRedirects(true)
        .followSslRedirects(true)
        .build();

    private LiveSiteFetcher() {}

    static boolean shouldProxy(String method, String host) {
        if (!LiveSiteRequests.isLiveTalkieHost(host)) {
            return false;
        }

        return "GET".equalsIgnoreCase(method) || "HEAD".equalsIgnoreCase(method);
    }

    static WebResourceResponse fetch(WebResourceRequest request) {
        String url = request.getUrl().toString();

        try {
            Request.Builder builder = new Request.Builder().url(url).method(request.getMethod(), null);
            Map<String, String> headers = request.getRequestHeaders();
            if (headers != null) {
                for (Map.Entry<String, String> header : headers.entrySet()) {
                    String name = header.getKey();
                    if (name == null || skipRequestHeader(name)) {
                        continue;
                    }
                    builder.addHeader(name, header.getValue());
                }
            }

            String cookie = CookieManager.getInstance().getCookie(url);
            if (cookie != null && !cookie.isEmpty()) {
                builder.header("Cookie", cookie);
            }

            try (Response response = SITE_CLIENT.newCall(builder.build()).execute()) {
                for (String setCookie : response.headers("Set-Cookie")) {
                    CookieManager.getInstance().setCookie(url, setCookie);
                }

                ResponseBody body = response.body();
                byte[] bytes = body != null ? body.bytes() : new byte[0];
                String contentType = response.header("Content-Type", "application/octet-stream");
                String mimeType = mimeFromContentType(contentType);
                String encoding = charsetFromContentType(contentType);
                String reason = response.message();
                if (reason == null || reason.isEmpty()) {
                    reason = "OK";
                }

                return new WebResourceResponse(
                    mimeType,
                    encoding,
                    response.code(),
                    reason,
                    responseHeaders(response),
                    new ByteArrayInputStream(bytes)
                );
            }
        } catch (Exception ex) {
            Log.e(TAG, "Failed to load " + url, ex);
            return null;
        }
    }

    static String mimeFromContentType(String contentType) {
        if (contentType == null || contentType.isEmpty()) {
            return "application/octet-stream";
        }

        int separator = contentType.indexOf(';');
        String mime = separator == -1 ? contentType : contentType.substring(0, separator);

        return mime.trim().toLowerCase(Locale.US);
    }

    static String charsetFromContentType(String contentType) {
        if (contentType == null) {
            return "UTF-8";
        }

        String lower = contentType.toLowerCase(Locale.US);
        int charsetAt = lower.indexOf("charset=");
        if (charsetAt == -1) {
            return "UTF-8";
        }

        String value = contentType.substring(charsetAt + 8).trim();
        int semicolon = value.indexOf(';');
        if (semicolon != -1) {
            value = value.substring(0, semicolon).trim();
        }

        return value.replace("\"", "");
    }

    private static boolean skipRequestHeader(String name) {
        String lower = name.toLowerCase(Locale.US);

        return lower.equals("host")
            || lower.equals("connection")
            || lower.equals("content-length")
            || lower.equals("accept-encoding");
    }

    private static Map<String, String> responseHeaders(Response response) {
        Map<String, String> headers = new HashMap<>();
        for (int i = 0; i < response.headers().size(); i++) {
            String name = response.headers().name(i);
            if ("content-encoding".equalsIgnoreCase(name) || "transfer-encoding".equalsIgnoreCase(name)) {
                continue;
            }
            headers.put(name, response.headers().value(i));
        }

        return headers;
    }

    private static final class CachedDohDns implements Dns {
        private volatile List<InetAddress> cached;
        private volatile long cachedUntilMs;
        private volatile String cachedHost = "";

        @Override
        public List<InetAddress> lookup(String hostname) throws UnknownHostException {
            if (!LiveSiteRequests.isLiveTalkieHost(hostname)) {
                throw new UnknownHostException(hostname);
            }

            long now = System.currentTimeMillis();
            List<InetAddress> current = cached;
            if (current != null && hostname.equalsIgnoreCase(cachedHost) && now < cachedUntilMs) {
                return current;
            }

            synchronized (this) {
                current = cached;
                if (current != null && hostname.equalsIgnoreCase(cachedHost) && now < cachedUntilMs) {
                    return current;
                }

                try {
                    Request request = new Request.Builder().url(LiveSiteDns.dohQueryUrl(hostname)).build();
                    try (Response response = DOH_CLIENT.newCall(request).execute()) {
                        if (!response.isSuccessful() || response.body() == null) {
                            throw new UnknownHostException(hostname);
                        }

                        List<InetAddress> addresses = LiveSiteDns.addressesFromDohJson(hostname, response.body().string());
                        cached = addresses;
                        cachedHost = hostname;
                        cachedUntilMs = System.currentTimeMillis() + TimeUnit.MINUTES.toMillis(5);
                        return addresses;
                    }
                } catch (IOException | JSONException ex) {
                    UnknownHostException wrapped = new UnknownHostException(hostname);
                    wrapped.initCause(ex);
                    throw wrapped;
                }
            }
        }
    }
}
