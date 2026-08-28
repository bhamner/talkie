package kids.talkie;

import static org.junit.Assert.assertArrayEquals;
import static org.junit.Assert.assertEquals;
import static org.junit.Assert.assertTrue;

import java.net.InetAddress;
import java.util.List;
import org.junit.Test;

public class LiveSiteDnsTest {
    @Test
    public void parsesDohARecords() throws Exception {
        String json =
            "{\"Status\":0,\"Answer\":[" +
            "{\"name\":\"talkie.kids.\",\"type\":5,\"data\":\"alias.example.\"}," +
            "{\"name\":\"talkie.kids.\",\"type\":1,\"TTL\":300,\"data\":\"104.248.10.20\"}" +
            "]}";

        List<InetAddress> addresses = LiveSiteDns.addressesFromDohJson("talkie.kids", json);

        assertEquals(1, addresses.size());
        assertEquals("talkie.kids", addresses.get(0).getHostName());
        assertArrayEquals(new byte[] { 104, (byte) 248, 10, 20 }, addresses.get(0).getAddress());
    }

    @Test(expected = Exception.class)
    public void rejectsFailedDohStatus() throws Exception {
        LiveSiteDns.addressesFromDohJson("talkie.kids", "{\"Status\":3}");
    }

    @Test
    public void buildsDohQueryUrl() {
        assertTrue(LiveSiteDns.dohQueryUrl("Talkie.Kids").contains("name=talkie.kids"));
        assertTrue(LiveSiteDns.dohQueryUrl("talkie.kids").contains("type=A"));
    }

    @Test
    public void parsesContentType() {
        assertEquals("text/html", LiveSiteFetcher.mimeFromContentType("text/html; charset=UTF-8"));
        assertEquals("UTF-8", LiveSiteFetcher.charsetFromContentType("text/html; charset=UTF-8"));
        assertEquals("application/javascript", LiveSiteFetcher.mimeFromContentType("application/javascript"));
    }
}
