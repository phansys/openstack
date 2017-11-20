<?php declare(strict_types=1);

namespace OpenStack\Identity\v3\Models;

use OpenStack\Common\Resource\Alias;
use OpenStack\Common\Transport\Utils;
use Psr\Http\Message\ResponseInterface;
use OpenStack\Common\Resource\OperatorResource;
use OpenStack\Common\Resource\Creatable;
use OpenStack\Common\Resource\Retrievable;

/**
 * @property \OpenStack\Identity\v3\Api $api
 */
class Token extends OperatorResource implements Creatable, Retrievable, \OpenStack\Common\Auth\Token
{
    /** @var array */
    public $methods;

    /** @var Role[] */
    public $roles;

    /** @var \DateTimeImmutable */
    public $expires;

    /** @var Project */
    public $project;

    /** @var Catalog */
    public $catalog;

    /** @var mixed */
    public $extras;

    /** @var User */
    public $user;

    /** @var \DateTimeImmutable */
    public $issued;

    /** @var string */
    public $id;

    protected $resourceKey = 'token';
    protected $resourcesKey = 'tokens';

    protected $cacheCredential;

    /**
     * @inheritdoc
     */
    protected function getAliases(): array
    {
        return parent::getAliases() + [
            'roles'      => new Alias('roles', Role::class, true),
            'expires_at' => new Alias('expires', \DateTimeImmutable::class),
            'project'    => new Alias('project', Project::class),
            'catalog'    => new Alias('catalog', Catalog::class),
            'user'       => new Alias('user', User::class),
            'issued_at'  => new Alias('issued', \DateTimeImmutable::class)
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function populateFromResponse(ResponseInterface $response)
    {
        parent::populateFromResponse($response);
        $this->id = $response->getHeaderLine('X-Subject-Token');
        return $this;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return bool TRUE if the token has expired (and is invalid); FALSE otherwise.
     */
    public function hasExpired(): bool
    {
        return $this->expires <= new \DateTimeImmutable('now', $this->expires->getTimezone());
    }

    /**
     * {@inheritDoc}
     */
    public function retrieve()
    {
        $response = $this->execute($this->api->getTokens(), ['tokenId' => $this->id]);
        $this->populateFromResponse($response);
    }

    /**
     * {@inheritDoc}
     *
     * @param array $data {@see \OpenStack\Identity\v3\Api::postTokens}
     */
    public function create(array $data): Creatable
    {
        if (isset($data['user'])) {
            $data['methods'] = ['password'];
            if (!isset($data['user']['id']) && empty($data['user']['domain'])) {
                throw new \InvalidArgumentException(
                    'When authenticating with a username, you must also provide either the domain name or domain ID to '
                    . 'which the user belongs to. Alternatively, if you provide a user ID instead, you do not need to '
                    . 'provide domain information.'
                );
            }
        } elseif (isset($data['tokenId'])) {
            $data['methods'] = ['token'];
        } else {
            throw new \InvalidArgumentException('Either a user or token must be provided.');
        }

        $response = $this->execute($this->api->postTokens(), $data);
        $token = $this->populateFromResponse($response);

        $this->cacheCredential = Utils::flattenJson(Utils::jsonDecode($response), $this->resourceKey);
        $this->cacheCredential['id'] = $token->id;

        return $token;
    }

    /**
     * Retrieves an array serializable representation of authentication token.
     * Can be use to initialise OpenStack object using $params['cachedCredential']
     *
     * @return array
     */
    public function exportCredential(): array
    {
        return $this->cacheCredential;
    }
}
