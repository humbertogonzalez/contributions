<?php

namespace BalloonGroup\PearlCrack\Plugin;


/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */

class LicenseFix
{

    public function afterIsLcVd(\WeltPixel\Backend\Helper\License $subject){
        return true;
    }
}