<?php

namespace BalloonGroup\PearlCrack\Plugin;


/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */

class LicenseFixModel
{

    public function afterIsLcVd(\WeltPixel\Backend\Model\License $subject){
        return true;
    }
}