package kids.talkie;

import java.net.URI;
import java.net.URISyntaxException;

final class LiveSiteRequests {
    static final String LIVE_HOST = "talkie.kids";

    private LiveSiteRequests() {}

    static boolean isLiveTalkieHost(String host) {
        if (host == null || host.isEmpty()) {
            return false;
        }

        String normalized = host.toLowerCase();

        return normalized.equals(LIVE_HOST) || normalized.endsWith("." + LIVE_HOST);
    }

    static boolean shouldBypassProxy(String url) {
        if (url == null || url.isEmpty()) {
            return false;
        }

        try {
            return isLiveTalkieHost(new URI(url).getHost());
        } catch (URISyntaxException ignored) {
            return false;
        }
    }
}
