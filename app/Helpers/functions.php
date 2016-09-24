<?php
/**
 * Returns true if the resource is accessible
 *
 * @param $code
 * @return bool
 */
function can($code) {
    return \Access::can($code);
}

/**
 * Creates a comma separated list
 *
 * @param array $array
 * @return string
 */
function str_list($array) {
    if (!is_array($array)) {
        return $array;
    }

    $return = '';

    $i = 0;
    foreach ($array as $string) {
        if ($i > 0) {
            $return .= ', ';
        }

        if (count($array) == $i - 1) {
            $return .= 'and ';
        }

        $return .= $string;
        $i++;
    }

    return $return;
}