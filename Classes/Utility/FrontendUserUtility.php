<?php

declare(strict_types=1);

namespace Fixpunkt\FpFileprotector\Utility;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FrontendUserUtility
{
    /**
     * Returns the current frontend user aspect.
     *
     * @return UserAspect|null
     */
    public function getCurrentFrontendUser(): ?UserAspect
    {
        try {
            /** @var Context $context */
            $context = GeneralUtility::makeInstance(Context::class);
            /** @var UserAspect $userAspect */
            return $context->getAspect('frontend.user');
        } catch (\Exception) {
            return null;
        }
    }
}
