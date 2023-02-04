<?php
/**
 * OAuth 2.0 Bearer Token Response.
 *
 * @author      Alex Bilbie <hello@alexbilbie.com>
 * @copyright   Copyright (c) Alex Bilbie
 * @license     http://mit-license.org/
 *
 * @link        https://github.com/thephpleague/oauth2-server
 */

namespace League\OAuth2\Server\ResponseTypes;

use League\OAuth2\Server\Entities\DeviceCodeEntityInterface;
use LogicException;
use Psr\Http\Message\ResponseInterface;

class DeviceCodeResponse extends AbstractResponseType
{
    /**
     * @var DeviceCodeEntityInterface
     */
    protected $deviceCode;

    /**
     * @var string
     */
    protected $payload;

    /**
     * {@inheritdoc}
     */
    public function generateHttpResponse(ResponseInterface $response)
    {
        $expireDateTime = $this->deviceCode->getExpiryDateTime()->getTimestamp();

        // Required
        $responseParams = [
            'device_code' => $this->payload,
            'user_code' => $this->deviceCode->getUserCode(),
            'expires_in'   => $expireDateTime - \time(),
            'verification_uri' => $this->deviceCode->getVerificationUri(),
        ];

        // Optional
        if ($verificationUri = $responseParams['verification_uri']){
            $responseParams['verification_uri_complete'] = $verificationUri . '?user_code=' . $responseParams['user_code'];
        }
        $responseParams['interval'] = $this->deviceCode->getRetryInterval();

        $responseParams = \json_encode($responseParams);

        if ($responseParams === false) {
            throw new LogicException('Error encountered JSON encoding response parameters');
        }

        $response = $response
            ->withStatus(200)
            ->withHeader('pragma', 'no-cache')
            ->withHeader('cache-control', 'no-store')
            ->withHeader('content-type', 'application/json; charset=UTF-8');

        $response->getBody()->write($responseParams);

        return $response;
    }

    public function setPayload($payload)
    {
        $this->payload = $payload;
    }

    /**
     * {@inheritdoc}
     */
    public function setDeviceCode(DeviceCodeEntityInterface $deviceCode)
    {
        $this->deviceCode = $deviceCode;
    }

    /**
     * Add custom fields to your Bearer Token response here, then override
     * AuthorizationServer::getResponseType() to pull in your version of
     * this class rather than the default.
     *
     * @param DeviceCodeEntityInterface $deviceCode
     *
     * @return array
     */
    protected function getExtraParams(DeviceCodeEntityInterface $deviceCode)
    {
        return [];
    }
}