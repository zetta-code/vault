<?php
/**
 * @link      http://github.com/zetta-code/vault for the canonical source repository
 * @copyright Copyright (c) 2018 Zetta Code
 */

namespace Zetta\Vault\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zetta\Vault\Service\Crypt as CryptService;

/**
 * Class Crypt
 */
class Crypt extends AbstractHelper
{
    /**
     * @var CryptService
     */
    protected $cryptService;

    /**
     * Crypt constructor.
     * @param CryptService $cryptService
     */
    public function __construct(CryptService $cryptService)
    {
        $this->cryptService = $cryptService;
    }

    /**
     * @return CryptService
     */
    public function __invoke()
    {
        return $this->cryptService;
    }
}
