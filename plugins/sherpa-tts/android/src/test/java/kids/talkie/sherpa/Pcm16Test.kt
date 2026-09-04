package kids.talkie.sherpa

import org.junit.Assert.assertArrayEquals
import org.junit.Test

class Pcm16Test {
    @Test
    fun convertsFloatSamplesToPcm16() {
        val pcm = Pcm16.fromFloatSamples(floatArrayOf(0f, 1f, -1f, 0.5f))

        assertArrayEquals(shortArrayOf(0, 32767, -32767, 16383), pcm)
    }
}
