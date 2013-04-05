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
        $base_count = strlen($this->alphabet);
        $encoded = '';
        while ($num >= $base_count) {
            $div = $num/$base_count;
            $mod = ($num-($base_count*intval($div)));
            $encoded = $this->alphabet[$mod] . $encoded;
            $num = intval($div);
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
        $multi = 1;
        while (strlen($string) > 0) {
            $digit = $string[strlen($string)-1];
            $decoded += $multi * strpos($this->alphabet, $digit);
            $multi = $multi * strlen($this->alphabet);
            $string = substr($string, 0, -1);
        }
        return $decoded;
    }
}
