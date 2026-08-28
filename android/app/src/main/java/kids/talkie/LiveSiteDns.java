package kids.talkie;

import java.net.InetAddress;
import java.net.UnknownHostException;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.List;
import java.util.Locale;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

final class LiveSiteDns {
    private LiveSiteDns() {}

    static List<InetAddress> googleAnycast() throws UnknownHostException {
        return Arrays.asList(
            InetAddress.getByAddress("dns.google", new byte[] { 8, 8, 8, 8 }),
            InetAddress.getByAddress("dns.google", new byte[] { 8, 8, 4, 4 })
        );
    }

    static List<InetAddress> addressesFromDohJson(String hostname, String json) throws JSONException, UnknownHostException {
        JSONObject parsed = new JSONObject(json);
        if (parsed.optInt("Status", -1) != 0 || !parsed.has("Answer")) {
            throw new UnknownHostException(hostname);
        }

        JSONArray answers = parsed.getJSONArray("Answer");
        List<InetAddress> addresses = new ArrayList<>();
        for (int i = 0; i < answers.length(); i++) {
            JSONObject answer = answers.getJSONObject(i);
            if (answer.optInt("type") != 1) {
                continue;
            }

            String data = answer.optString("data", "");
            if (data.isEmpty()) {
                continue;
            }

            addresses.add(InetAddress.getByAddress(hostname, ipv4Bytes(data)));
        }

        if (addresses.isEmpty()) {
            throw new UnknownHostException(hostname);
        }

        return addresses;
    }

    static byte[] ipv4Bytes(String dotted) throws UnknownHostException {
        String[] parts = dotted.trim().split("\\.");
        if (parts.length != 4) {
            throw new UnknownHostException(dotted);
        }

        byte[] bytes = new byte[4];
        for (int i = 0; i < 4; i++) {
            int value = Integer.parseInt(parts[i]);
            if (value < 0 || value > 255) {
                throw new UnknownHostException(dotted);
            }
            bytes[i] = (byte) value;
        }

        return bytes;
    }

    static String dohQueryUrl(String hostname) {
        return "https://dns.google/resolve?name=" + hostname.toLowerCase(Locale.US) + "&type=A";
    }
}
