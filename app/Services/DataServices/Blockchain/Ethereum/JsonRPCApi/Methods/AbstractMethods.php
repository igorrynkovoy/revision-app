<?php

namespace App\Services\DataServices\Blockchain\Ethereum\JsonRPCApi\Methods;

use App\Exceptions\DataServices\Ethereum\JsonRPCApi\RPCApiException;
use Graze\GuzzleHttp\JsonRpc\ClientInterface;
use Graze\GuzzleHttp\JsonRpc\Message\RequestInterface;
use Graze\GuzzleHttp\JsonRpc\Message\ResponseInterface;
use Illuminate\Support\Str;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

abstract class AbstractMethods
{
    protected $client;
    protected $logger;
    protected $disableLogs = false;
    /** @var ResponseInterface */
    protected $lastResponse;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;

        $handler = new StreamHandler(storage_path('logs/ethereum.log'), Logger::DEBUG);
        $handler->setFormatter(new LineFormatter(null, null, true, true));

        $this->logger = new Logger('ethereum_logger', [$handler]);
    }

    public function disableLogs()
    {
        $this->disableLogs = true;
    }

    public function send(RequestInterface $request)
    {
        $id = $request->getRpcId();
        $method = $request->getRpcMethod();
        $parameters = $request->getRpcParams();

        $request = $request->withAddedHeader('Content-Type', 'application/json');
        $request = $request->withAddedHeader('Authorization', 'Basic ' . base64_encode(config('services.ethereum.login') . ":" . config('services.ethereum.password')));
        dump($request->getBody()->getContents());
        $response = $this->client->send($request);

        $rawResponse = $response->getBody()->getContents();

        if(!$this->disableLogs) {
            $this->logger->info('Request ' . $method . ' (' . $id . ') ' . (is_null($response->getRpcErrorCode()) ? ' failed' : ' success') . '.', [
                'rpc_id' => $id,
                'method' => $method,
                'parameters' => $parameters,
                'error_code' => $response->getRpcErrorCode(),
                'error_message' => $response->getRpcErrorMessage(),
                'response' => Str::limit($rawResponse, 1024)
            ]);
        }

        if(!is_null($response->getRpcErrorCode())) {
            throw new RPCApiException($response->getRpcErrorMessage(), $response->getRpcErrorCode());
        }

        $this->lastResponse = $response;

        return $response;
    }

    /**
     * @return ResponseInterface
     */
    public function getLastResponse()
    {
        return $this->lastResponse;
    }
}
