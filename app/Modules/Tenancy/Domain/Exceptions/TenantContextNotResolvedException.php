<?php

namespace App\Modules\Tenancy\Domain\Exceptions;

use RuntimeException;

class TenantContextNotResolvedException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Tenant context has not been resolved from authenticated identity.');
    }
}
