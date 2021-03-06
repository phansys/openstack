<?php declare (strict_types=1);

namespace OpenStack\Identity\v2\Models;

use OpenCloud\Common\Transport\Utils;
use Psr\Http\Message\ResponseInterface;
use OpenCloud\Common\Resource\OperatorResource;
use OpenCloud\Common\Resource\ValueResource;

/**
 * Represents an Identity v2 Token.
 *
 * @package OpenStack\Identity\v2\Models
 */
class Token extends OperatorResource implements \OpenCloud\Common\Auth\Token
{
    /** @var \DateTimeImmutable */
    public $issuedAt;

    /** @var string */
    public $id;

    /** @var \DateTimeImmutable */
    public $expires;

    /** @var Tenant */
    public $tenant;

    protected $aliases = ['issued_at' => 'issuedAt'];

    /**
     * {@inheritDoc}
     */
    public function populateFromResponse(ResponseInterface $response): self
    {
        $this->populateFromArray(Utils::jsonDecode($response)['access']['token']);

        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function hasExpired(): bool
    {
        return $this->expires <= new \DateTimeImmutable('now', $this->expires->getTimezone());
    }
}
