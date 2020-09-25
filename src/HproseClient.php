<?php


namespace Lxj\Yii2\Zipkin;


use Exception;
use Hprose\Http\Client;
use stdClass;
use yii\console\Application;
use Zipkin\Span;
use const Zipkin\Tags\HTTP_HOST;
use const Zipkin\Tags\HTTP_METHOD;
use const Zipkin\Tags\HTTP_PATH;
use const Zipkin\Tags\HTTP_STATUS_CODE;

class HproseClient extends Client
{
    public function __construct($uris = null, $async = true)
    {
        parent::__construct($uris, $async);
    }

    /**
     * @param $request
     * @param stdClass $context
     * @return bool|string
     * @throws Exception
     */
    protected function syncSendAndReceive($request, stdClass $context) {
        $sendRequest = function ($curl, $context) {
            try {
                $data = curl_exec($curl);
                $errno = curl_errno($curl);
                if ($errno) {
                    throw new Exception($errno . ": " . curl_error($curl));
                }
                $data = $this->getContents($data, $context);
                curl_close($curl);
                return $data;
            } catch (\Exception $e) {
                \Yii::error('CURL ERROR ' . $e->getMessage(), 'zipkin');
                throw new \Exception('CURL ERROR ' . $e->getMessage());
            }
        };

        /** @var Tracer $yiiTracer */
        $yiiTracer = \Yii::$app->zipkin;
        $requestInfo = parse_url($this->uri);
        $path = $requestInfo[PHP_URL_PATH];
        return $yiiTracer->clientSpan(
            isset($spanName) ? $spanName : $yiiTracer->formatRoutePath($path),
            function (Span $span) use ($request, $sendRequest, $yiiTracer, $path, $context){
                $curl = curl_init();
                //Inject trace context to api psr request
                if (true) {
                    $yiiTracer->injectContextToRequest($span->getContext(), $this);
                }

                $this->initCurl($curl, $request, $context);

                if ($span->getContext()->isSampled()) {
                    $yiiTracer->addTag($span, HTTP_HOST, $requestInfo[PHP_URL_HOST]);
                    $yiiTracer->addTag($span, HTTP_PATH, $path);
                    $yiiTracer->addTag($span, Tracer::HTTP_QUERY_STRING, (string)$requestInfo[PHP_URL_QUERY]);
                    $yiiTracer->addTag($span, HTTP_METHOD, 'POST');

                    $yiiTracer->addTag($span, Tracer::HTTP_REQUEST_BODY_SIZE, strlen($request));
                    $yiiTracer->addTag($span, Tracer::HTTP_REQUEST_BODY, $request);
                    //$yiiTracer->addTag($span, Tracer::HTTP_REQUEST_HEADERS, json_encode(, JSON_UNESCAPED_UNICODE));
                    $yiiTracer->addTag(
                        $span,
                        Tracer::HTTP_REQUEST_PROTOCOL_VERSION,
                        $yiiTracer->formatHttpProtocolVersion(1.1)
                    );
                    $yiiTracer->addTag($span, Tracer::HTTP_REQUEST_SCHEME, $requestInfo[PHP_URL_SCHEME]);
                }

                $response = null;
                try {
                    $response = $sendRequest($curl, $context);//call_user_func($sendRequest);
                    return $response;
                } catch (\Exception $e) {
                    throw $e;
                } finally {
                    if ($response) {
                        if ($span->getContext()->isSampled()) {
                            $yiiTracer->addTag($span, HTTP_STATUS_CODE, $context->httpHeader['status']);
                            $httpResponseBodyLen = strlen($response);
                            $yiiTracer->addTag($span, Tracer::HTTP_RESPONSE_BODY_SIZE, $httpResponseBodyLen);
                            //$yiiTracer->addTag($span, Tracer::HTTP_RESPONSE_BODY, $yiiTracer->formatHttpBody($response, $httpResponseBodyLen));
                            $yiiTracer->addTag($span, Tracer::HTTP_RESPONSE_HEADERS, json_encode($context->httpHeader, JSON_UNESCAPED_UNICODE));
                            $yiiTracer->addTag(
                                $span,
                                Tracer::HTTP_RESPONSE_PROTOCOL_VERSION,
                                $yiiTracer->formatHttpProtocolVersion(1.1)
                            );
                        }
                    }
                }
            }, true);
    }

    public function withAddedHeader($key, $value) {
        $this->setHeader($key, $value);
        return $this;
    }


}