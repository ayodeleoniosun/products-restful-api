<?php

namespace App\Util;

use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Symfony\Component\HttpFoundation\Request;

class JWT
{
    public function __construct(public JWTEncoderInterface $JWTEncoder)
    {

    }

    /**
     * @throws JWTDecodeFailureException
     */
    public function decodeToken(Request $request): array
    {
        $header = $request->headers->get('Authorization');
        $token = explode(" ", $header)[1];
        return $this->JWTEncoder->decode($token);
    }


}