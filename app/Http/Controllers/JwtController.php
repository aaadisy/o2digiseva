<?php
namespace App\Http\Controllers;
use Exception;


class JwtController extends Controller
{

    
    public static $leeway = 0;

    
    public static $timestamp = null;

    public static $supported_algs = array(
        'HS256' => array('hash_hmac', 'SHA256'),
        'HS512' => array('hash_hmac', 'SHA512'),
        'HS384' => array('hash_hmac', 'SHA384'),
        'RS256' => array('openssl', 'SHA256'),
        'RS384' => array('openssl', 'SHA384'),
        'RS512' => array('openssl', 'SHA512'),
    );

    public static function generateToken(){
        $key = "UFMwMDcxOTZiZjI5NjFkMWU2YzcxOTFmYmNiYjIwYmRkMDM5YzQ=";
        //JwtController::$leeway = 600;
        $ran = random_int(100000, 999999);
        $jwtpayload = ["timestamp" => time(), "partnerId" => "PS0050", "reqid" => "$ran"];
        $jwtToken = JwtController::encode($jwtpayload, $key);
        return $jwtToken;
    }

    public static function callApi($payload, $url){
        $key = "UFMwMDcxOTZiZjI5NjFkMWU2YzcxOTFmYmNiYjIwYmRkMDM5YzQ=";
        //JwtController::$leeway = 600;
        $ran = random_int(100000, 999999);
        $jwtpayload = ["timestamp" => time(), "partnerId" => "PS0050", "reqid" => "$ran"];
        $jwtToken = JwtController::encode($jwtpayload, $key);
        //$jwtToken = JwtController::decode($jwtToken, $key, ['HS256']);
        
        //dd($jwtToken); exit;
        if(is_array($payload) && !empty($payload)){
            
            $curl = curl_init();

            curl_setopt_array($curl, array(
              CURLOPT_URL => $url,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'POST',
              CURLOPT_POSTFIELDS => $payload,
              CURLOPT_HTTPHEADER => array(
                'Token: '.$jwtToken,
                'Authorisedkey: ZmIwYjE3ODcyYmIzZWM0OWI1ZGMzZjMzZDY5MmJkODc=',
                'key: UFMwMDcxOTZiZjI5NjFkMWU2YzcxOTFmYmNiYjIwYmRkMDM5YzQ=',
                'Cookie: ci_session=bbdf8dc51f0964daca1dbe38746a4a9f5f58a0dd'
              ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
          }else{
            $curl = curl_init();

            curl_setopt_array($curl, array(
              CURLOPT_URL => $url,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'POST',
              CURLOPT_HTTPHEADER => array(
                'Token: '.$jwtToken,
                'Authorisedkey: ZmIwYjE3ODcyYmIzZWM0OWI1ZGMzZjMzZDY5MmJkODc=',
                'key: UFMwMDcxOTZiZjI5NjFkMWU2YzcxOTFmYmNiYjIwYmRkMDM5YzQ=',
                'Cookie: ci_session=bbdf8dc51f0964daca1dbe38746a4a9f5f58a0dd'
              ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
        }
        //echo $response;



        return $response;
    }

    public static function callApiWithoutParamGet($url){
        $key = "UFMwMDcxOTZiZjI5NjFkMWU2YzcxOTFmYmNiYjIwYmRkMDM5YzQ=";
        $ran = random_int(100000, 999999);
        $jwtpayload = ["timestamp" => time(), "partnerId" => "PS0050", "reqid" => "$ran"];
        $jwtToken = JwtController::encode($jwtpayload, $key);
        
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');


        $headers = array();
        $headers[] = 'Token: '.$jwtToken;
        $headers[] = 'Authorisedkey: ZmIwYjE3ODcyYmIzZWM0OWI1ZGMzZjMzZDY5MmJkODc=';
        $headers[] = 'key: UFMwMDcxOTZiZjI5NjFkMWU2YzcxOTFmYmNiYjIwYmRkMDM5YzQ=';
        $headers[] = 'Cookie: ci_session=bbdf8dc51f0964daca1dbe38746a4a9f5f58a0dd';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        return $result;
    }

    
    public static function decode($jwt, $key, array $allowed_algs = array())
    {
        $timestamp = is_null(static::$timestamp) ? time() : static::$timestamp;

        if (empty($key)) {
            throw new InvalidArgumentException('Key may not be empty');
        }
        $tks = explode('.', $jwt);
        if (count($tks) != 3) {
            throw new UnexpectedValueException('Wrong number of segments');
        }
        list($headb64, $bodyb64, $cryptob64) = $tks;
        if (null === ($header = static::jsonDecode(static::urlsafeB64Decode($headb64)))) {
            throw new UnexpectedValueException('Invalid header encoding');
        }
        if (null === $payload = static::jsonDecode(static::urlsafeB64Decode($bodyb64))) {
            throw new UnexpectedValueException('Invalid claims encoding');
        }
        if (false === ($sig = static::urlsafeB64Decode($cryptob64))) {
            throw new UnexpectedValueException('Invalid signature encoding');
        }
        if (empty($header->alg)) {
            throw new UnexpectedValueException('Empty algorithm');
        }
        if (empty(static::$supported_algs[$header->alg])) {
            throw new UnexpectedValueException('Algorithm not supported');
        }
        if (!in_array($header->alg, $allowed_algs)) {
            throw new UnexpectedValueException('Algorithm not allowed');
        }
        if (is_array($key) || $key instanceof \ArrayAccess) {
            if (isset($header->kid)) {
                if (!isset($key[$header->kid])) {
                    throw new UnexpectedValueException('"kid" invalid, unable to lookup correct key');
                }
                $key = $key[$header->kid];
            } else {
                throw new UnexpectedValueException('"kid" empty, unable to lookup correct key');
            }
        }

        
        if (!static::verify("$headb64.$bodyb64", $sig, $key, $header->alg)) {
           
            throw new UnexpectedValueException('Signature verification failed');
        }

     
        if (isset($payload->nbf) && $payload->nbf > ($timestamp + static::$leeway)) {
            throw new UnexpectedValueException(
                'Cannot handle token prior to ' . date(DateTime::ISO8601, $payload->nbf)
            );
        }

        
        if (isset($payload->iat) && $payload->iat > ($timestamp + static::$leeway)) {
            throw new UnexpectedValueException(
                'Cannot handle token prior to ' . date(DateTime::ISO8601, $payload->iat)
            );
        }

        
        if (isset($payload->exp) && ($timestamp - static::$leeway) >= $payload->exp) {
            throw new UnexpectedValueException('Expired token');
        }

        return $payload;
    }

    
    public static function encode($payload, $key, $alg = 'HS256', $keyId = null, $head = null)
    {
        $header = array('typ' => 'JWT', 'alg' => $alg);
        if ($keyId !== null) {
            $header['kid'] = $keyId;
        }
        if ( isset($head) && is_array($head) ) {
            $header = array_merge($head, $header);
        }
        $segments = array();
        $segments[] = static::urlsafeB64Encode(static::jsonEncode($header));
        $segments[] = static::urlsafeB64Encode(static::jsonEncode($payload));
        $signing_input = implode('.', $segments);

        $signature = static::sign($signing_input, $key, $alg);
        $segments[] = static::urlsafeB64Encode($signature);

        return implode('.', $segments);
    }

    
    public static function sign($msg, $key, $alg = 'HS256')
    {
        if (empty(static::$supported_algs[$alg])) {
            throw new DomainException('Algorithm not supported');
        }
        list($function, $algorithm) = static::$supported_algs[$alg];
        switch($function) {
            case 'hash_hmac':
                return hash_hmac($algorithm, $msg, $key, true);
            case 'openssl':
                $signature = '';
                $success = openssl_sign($msg, $signature, $key, $algorithm);
                if (!$success) {
                    throw new DomainException("OpenSSL unable to sign data");
                } else {
                    return $signature;
                }
        }
    }

    
    private static function verify($msg, $signature, $key, $alg)
    {
        if (empty(static::$supported_algs[$alg])) {
            throw new DomainException('Algorithm not supported');
        }

        list($function, $algorithm) = static::$supported_algs[$alg];
        switch($function) {
            case 'openssl':
                $success = openssl_verify($msg, $signature, $key, $algorithm);
                if ($success === 1) {
                    return true;
                } elseif ($success === 0) {
                    return false;
                }
                // returns 1 on success, 0 on failure, -1 on error.
                throw new DomainException(
                    'OpenSSL error: ' . openssl_error_string()
                );
            case 'hash_hmac':
            default:
                $hash = hash_hmac($algorithm, $msg, $key, true);
                if (function_exists('hash_equals')) {
                    return hash_equals($signature, $hash);
                }
                $len = min(static::safeStrlen($signature), static::safeStrlen($hash));

                $status = 0;
                for ($i = 0; $i < $len; $i++) {
                    $status |= (ord($signature[$i]) ^ ord($hash[$i]));
                }
                $status |= (static::safeStrlen($signature) ^ static::safeStrlen($hash));

                return ($status === 0);
        }
    }

   
    public static function jsonDecode($input)
    {
        if (version_compare(PHP_VERSION, '5.4.0', '>=') && !(defined('JSON_C_VERSION') && PHP_INT_SIZE > 4)) {
            
            $obj = json_decode($input, false, 512, JSON_BIGINT_AS_STRING);
        } else {
            
            $max_int_length = strlen((string) PHP_INT_MAX) - 1;
            $json_without_bigints = preg_replace('/:\s*(-?\d{'.$max_int_length.',})/', ': "$1"', $input);
            $obj = json_decode($json_without_bigints);
        }

        if (function_exists('json_last_error') && $errno = json_last_error()) {
            static::handleJsonError($errno);
        } elseif ($obj === null && $input !== 'null') {
            throw new DomainException('Null result with non-null input');
        }
        return $obj;
    }

    
    public static function jsonEncode($input)
    {
        $json = json_encode($input);
        if (function_exists('json_last_error') && $errno = json_last_error()) {
            static::handleJsonError($errno);
        } elseif ($json === 'null' && $input !== null) {
            throw new DomainException('Null result with non-null input');
        }
        return $json;
    }

    
    public static function urlsafeB64Decode($input)
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }

    
    public static function urlsafeB64Encode($input)
    {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }

    
    private static function handleJsonError($errno)
    {
        $messages = array(
            JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
            JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON',
            JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
            JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON',
            JSON_ERROR_UTF8 => 'Malformed UTF-8 characters' //PHP >= 5.3.3
        );
        throw new DomainException(
            isset($messages[$errno])
            ? $messages[$errno]
            : 'Unknown JSON error: ' . $errno
        );
    }

    private static function safeStrlen($str)
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($str, '8bit');
        }
        return strlen($str);
    }
}