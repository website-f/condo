<?php

namespace FSPoster\App\SocialNetworks\Twitter\Api\CookieMethod;

class Cubic
{
    private array $curves;

    public function __construct(array $curves)
    {
        $this->curves = $curves;
    }

    public function getValue($time)
    {
        $startGradient = 0.0;
        $endGradient = 0.0;
        $start = 0.0;
        $mid = 0.0;
        $end = 1.0;

        if ($time <= 0.0) {
            if ($this->curves[0] > 0.0) {
                $startGradient = $this->curves[1] / $this->curves[0];
            } else if ($this->curves[1] == 0.0 && $this->curves[2] > 0.0) {
                $startGradient = $this->curves[3] / $this->curves[2];
            }

            return $startGradient * $time;
        }

        if ($time >= 1.0) {
            if ($this->curves[2] < 1.0) {
                $endGradient = ($this->curves[3] - 1.0) / ($this->curves[2] - 1.0);
            } else if ($this->curves[2] == 1.0 && $this->curves[0] < 1.0) {
                $endGradient = ($this->curves[1] - 1.0) / ($this->curves[0] - 1.0);
            }

            return 1.0 + $endGradient * ($time - 1.0);
        }

        while ($start < $end) {
            $mid = ($start + $end) / 2;
            $xEst = self::calculate($this->curves[0], $this->curves[2], $mid);

            if (abs($time - $xEst) < 0.00001) {
                return self::calculate($this->curves[1], $this->curves[3], $mid);
            }

            if ($xEst < $time) {
                $start = $mid;
            } else {
                $end = $mid;
            }
        }

        return self::calculate($this->curves[1], $this->curves[3], $mid);
    }

    public static function calculate($a, $b, $m): float
    {
        return 3.0 * $a * (1 - $m) * (1 - $m) * $m + 3.0 * $b * (1 - $m) * $m * $m + $m * $m * $m;
    }
}
