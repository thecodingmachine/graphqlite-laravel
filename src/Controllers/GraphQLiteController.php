<?php


namespace TheCodingMachine\GraphQLite\Laravel\Controllers;


use Illuminate\Http\Request;
use GraphQL\Error\Debug;
use GraphQL\Executor\ExecutionResult;
use GraphQL\Executor\Promise\Promise;
use GraphQL\Server\StandardServer;
use GraphQL\Upload\UploadMiddleware;
use TheCodingMachine\GraphQLite\Http\HttpCodeDecider;
use function array_map;
use function json_decode;
use function json_last_error;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use function max;


class GraphQLiteController
{
    /**
     * @var HttpMessageFactoryInterface
     */
    private $httpMessageFactory;
    /** @var StandardServer */
    private $standardServer;
    /** @var bool|int */
    private $debug;

    public function __construct(StandardServer $standardServer, HttpMessageFactoryInterface $httpMessageFactory = null, ?int $debug = Debug::RETHROW_UNSAFE_EXCEPTIONS)
    {
        $this->standardServer = $standardServer;
        $this->httpMessageFactory = $httpMessageFactory ?: new DiactorosFactory();
        $this->debug = $debug === null ? false : $debug;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function index(Request $request): JsonResponse
    {
        $psr7Request = $this->httpMessageFactory->createRequest($request);

        if (strtoupper($request->getMethod()) === "POST" && empty($psr7Request->getParsedBody())) {
            $content = $psr7Request->getBody()->getContents();
            $parsedBody = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Invalid JSON received in POST body: '.json_last_error_msg());
            }
            $psr7Request = $psr7Request->withParsedBody($parsedBody);
        }

        if (class_exists('\GraphQL\Upload\UploadMiddleware')) {
            // Let's parse the request and adapt it for file uploads.
            $uploadMiddleware = new UploadMiddleware();
            $psr7Request = $uploadMiddleware->processRequest($psr7Request);
        }

        return $this->handlePsr7Request($psr7Request);
    }

    private function handlePsr7Request(ServerRequestInterface $request): JsonResponse
    {
        $result = $this->standardServer->executePsrRequest($request);

        $httpCodeDecider = new HttpCodeDecider();
        if ($result instanceof ExecutionResult) {
            return new JsonResponse($result->toArray($this->debug), $httpCodeDecider->decideHttpStatusCode($result));
        }
        if (is_array($result)) {
            $finalResult =  array_map(function (ExecutionResult $executionResult) {
                return new JsonResponse($executionResult->toArray($this->debug));
            }, $result);
            // Let's return the highest result.
            $statuses = array_map([$httpCodeDecider, 'decideHttpStatusCode'], $result);
            $status = max($statuses);
            return new JsonResponse($finalResult, $status);
        }
        if ($result instanceof Promise) {
            throw new RuntimeException('Only SyncPromiseAdapter is supported');
        }
        throw new RuntimeException('Unexpected response from StandardServer::executePsrRequest'); // @codeCoverageIgnore
    }
}
