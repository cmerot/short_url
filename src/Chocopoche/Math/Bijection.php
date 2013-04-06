<?php
namespace Chocopoche\Math;

/**
 * Bijective helper to encode integers to string and vice versa.
 *
 * @see http://www.flickr.com/groups/api/discuss/72157616713786392/
 *
 * @author  Corentin Merot <co@tmb.io>
 */
class Bijection
{
    protected $alphabet;

    /**
     * Constructor.
     *
     * @param string $alphabet   The alphabet used for bijection
     */
    public function __construct($alphabet) {
        $this->alphabet = $alphabet;
    }

    /**
     * Encodes an integer to a string.
     *
     * @param integer The integer to encode
     *
     * @return string The encoded integer
     */
    public function encode($num) {
        $num = $this->computeHash($num);
        $base_count = strlen($this->alphabet);
        $encoded    = '';
        while ($num >= $base_count) {
            $div     = $num / $base_count;
            $mod     = ($num - ($base_count * intval($div)));
            $encoded = $this->alphabet[$mod] . $encoded;
            $num     = intval($div);
        }

        if ($num) $encoded = $this->alphabet[$num] . $encoded;

        return $encoded;
    }

    /**
     * Decodes a string to an integer.
     *
     * @param string The string to decode
     *
     * @return integer The decoded sting
     */
    public function decode($string) {
        $decoded = 0;
        $multi   = 1;
        while (strlen($string) > 0) {
            $digit    = $string[strlen($string) - 1];
            $decoded += $multi * strpos($this->alphabet, $digit);
            $multi    = $multi * strlen($this->alphabet);
            $string   = substr($string, 0, -1);
        }
        return $this->computeHash($decoded);
    }

    /**
     * This function computes a hash of an integer. This can be used to not expose values to a customer, such as
     * not giving them the id value for passing them to URLs. This algorithm is a bidirectional encryption (Feistel cipher) that maps
     * the integer space onto itself.
     *
     * @link https://gist.github.com/baldurrensch/3710618
     * @link http://wiki.postgresql.org/wiki/Pseudo_encrypt Algorithm used
     * @link http://en.wikipedia.org/wiki/Feistel_cipher Wikipedia page about Feistel ciphers
     * @param int $value
     * @return int
     * @author Baldur Rensch <brensch@gmail.com>
     */
    function computeHash($value)
    {
        $l1 = ($value >> 16) & 65535;
        $r1 = $value & 65535;
        for ($i = 0; $i < 3; $i++) {
            $l2 = $r1;
            $r2 = $l1 ^ (int) ((((1366 * $r1 + 150889) % 714025) / 714025) * 32767);
            $l1 = $l2;
            $r1 = $r2;
        }
        return ($r1 << 16) + $l1;
    }
}

