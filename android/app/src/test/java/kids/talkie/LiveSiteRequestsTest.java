package kids.talkie;

import static org.junit.Assert.assertFalse;
import static org.junit.Assert.assertTrue;

import org.junit.Test;

public class LiveSiteRequestsTest {
    @Test
    public void matchesLiveHostAndSubdomains() {
        assertTrue(LiveSiteRequests.isLiveTalkieHost("talkie.kids"));
        assertTrue(LiveSiteRequests.isLiveTalkieHost("Talkie.Kids"));
        assertTrue(LiveSiteRequests.isLiveTalkieHost("www.talkie.kids"));
    }

    @Test
    public void rejectsOtherHosts() {
        assertFalse(LiveSiteRequests.isLiveTalkieHost(null));
        assertFalse(LiveSiteRequests.isLiveTalkieHost(""));
        assertFalse(LiveSiteRequests.isLiveTalkieHost("localhost"));
        assertFalse(LiveSiteRequests.isLiveTalkieHost("google.com"));
        assertFalse(LiveSiteRequests.isLiveTalkieHost("nottalkie.kids"));
    }

    @Test
    public void bypassesLiveSiteUrlsOnly() {
        assertTrue(LiveSiteRequests.shouldBypassProxy("https://talkie.kids/"));
        assertTrue(LiveSiteRequests.shouldBypassProxy("https://talkie.kids/board"));
        assertTrue(LiveSiteRequests.shouldBypassProxy("https://www.talkie.kids/"));
        assertFalse(LiveSiteRequests.shouldBypassProxy("https://localhost/error.html"));
        assertFalse(LiveSiteRequests.shouldBypassProxy("https://google.com/"));
        assertFalse(LiveSiteRequests.shouldBypassProxy("not a url"));
    }

    @Test
    public void proxiesSafeLiveSiteMethods() {
        assertTrue(LiveSiteFetcher.shouldProxy("GET", "talkie.kids"));
        assertTrue(LiveSiteFetcher.shouldProxy("HEAD", "www.talkie.kids"));
        assertFalse(LiveSiteFetcher.shouldProxy("POST", "talkie.kids"));
        assertFalse(LiveSiteFetcher.shouldProxy("GET", "google.com"));
    }
}
