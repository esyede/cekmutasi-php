<?php

namespace Esyede\CekMutasi;

class Curl
{
    protected $curlObject = null;
    protected $curlOptions = [
        'RETURNTRANSFER' => true,
        'FAILONERROR' => false,
        'FOLLOWLOCATION' => false,
        'CONNECTTIMEOUT' => 30,
        'TIMEOUT' => 30,
        'USERAGENT' => '',
        'URL' => '',
        'POST' => false,
        'HTTPHEADER' => [],
        'SSL_VERIFYPEER' => false,
        'NOBODY' => false,
        'HEADER' => false,
    ];

    protected $builderOptions = [
        'data' => [],
        'files' => [],
        'asJsonRequest' => false,
        'asJsonResponse' => false,
        'returnAsArray' => false,
        'responseObject' => false,
        'responseArray' => false,
        'enableDebug' => false,
        'xDebugSessionName' => '',
        'containsFile' => false,
        'debugFile' => '',
        'saveFile' => '',
    ];


    public function to($url)
    {
        return $this->withCurlOption('URL', $url);
    }


    public function withTimeout($timeout = 60.0)
    {
        return $this->withCurlOption('TIMEOUT_MS', $timeout * 1000);
    }


    public function withConnectTimeout($timeout = 60.0)
    {
        return $this->withCurlOption('CONNECTTIMEOUT_MS', $timeout * 1000);
    }


    public function withData($data = [])
    {
        return $this->withBuilderOption('data', $data);
    }


    public function withFile($key, $path, $mimeType = '', $postFileName = '')
    {
        $fileData = [
            'fileName' => $path,
            'mimeType' => $mimeType,
            'postFileName' => $postFileName,
        ];

        $this->builderOptions['files'][$key] = $fileData;

        return $this->containsFile();
    }


    public function allowRedirect()
    {
        return $this->withCurlOption('FOLLOWLOCATION', true);
    }


    public function asJson($asArray = false)
    {
        return $this->asJsonRequest()->asJsonResponse($asArray);
    }


    public function asJsonRequest()
    {
        return $this->withBuilderOption('asJsonRequest', true);
    }


    public function asJsonResponse($asArray = false)
    {
        return $this->withBuilderOption('asJsonResponse', true)->withBuilderOption('returnAsArray', $asArray);
    }


    public function withOption($key, $value)
    {
        return $this->withCurlOption($key, $value);
    }


    public function setCookieFile($cookieFile)
    {
        return $this->withOption('COOKIEFILE', $cookieFile);
    }


    public function setCookieJar($cookieJar)
    {
        return $this->withOption('COOKIEJAR', $cookieJar);
    }


    protected function withCurlOption($key, $value)
    {
        $this->curlOptions[$key] = $value;
        return $this;
    }


    protected function withBuilderOption($key, $value)
    {
        $this->builderOptions[$key] = $value;
        return $this;
    }


    public function withHeader($header)
    {
        $this->curlOptions['HTTPHEADER'][] = $header;
        return $this;
    }


    public function withHeaders(array $headers)
    {
        $data = [];

        foreach ($headers as $key => $value) {
            if (! is_numeric($key)) {
                $value = $key . ': ' . $value;
            }

            $data[] = $value;
        }

        $this->curlOptions['HTTPHEADER'] = array_merge($this->curlOptions['HTTPHEADER'], $data);
        return $this;
    }


    public function withAuthorization($token)
    {
        return $this->withHeader('Authorization: ' . $token);
    }


    public function withBearer($bearer)
    {
        return $this->withAuthorization('Bearer ' . $bearer);
    }


    public function withContentType($contentType)
    {
        return $this->withHeader('Content-Type: ' . $contentType)->withHeader('Connection: Keep-Alive');
    }


    public function withResponseHeaders()
    {
        return $this->withCurlOption('HEADER', true);
    }


    public function returnResponseObject()
    {
        return $this->withBuilderOption('responseObject', true);
    }


    public function returnResponseArray()
    {
        return $this->withBuilderOption('responseArray', true);
    }


    public function enableDebug($logFile)
    {
        return $this->withBuilderOption('enableDebug', true)
            ->withBuilderOption('debugFile', $logFile)
            ->withOption('VERBOSE', true);
    }


    public function withProxy($proxy, $port = '', $type = '', $username = '', $password = '')
    {
        $this->withOption('PROXY', $proxy);

        if (! empty($port)) {
            $this->withOption('PROXYPORT', $port);
        }

        if (! empty($type)) {
            $this->withOption('PROXYTYPE', $type);
        }

        if (! empty($username) && ! empty($password)) {
            $this->withOption('PROXYUSERPWD', $username . ':' . $password);
        }

        return $this;
    }


    public function containsFile()
    {
        return $this->withBuilderOption('containsFile', true);
    }


    public function enableXDebug($sessionName = 'session_1')
    {
        $this->builderOptions['xDebugSessionName'] = $sessionName;
        return $this;
    }


    public function get()
    {
        $this->appendDataToURL();
        return $this->send();
    }


    public function post()
    {
        $this->setPostParameters();
        return $this->send();
    }


    public function download($fileName)
    {
        $this->appendDataToURL();
        $this->builderOptions['saveFile'] = $fileName;
        return $this->send();
    }


    protected function setPostParameters()
    {
        $this->curlOptions['POST'] = true;
        $parameters = $this->builderOptions['data'];

        if (! empty($this->builderOptions['files'])) {
            foreach ($this->builderOptions['files'] as $key => $file) {
                $parameters[$key] = $this->getCurlFileValue($file['fileName'], $file['mimeType'], $file['postFileName']);
            }
        }

        if ($this->builderOptions['asJsonRequest']) {
            $parameters = json_encode($parameters);
        }

        $this->curlOptions['POSTFIELDS'] = $parameters;
    }

    protected function getCurlFileValue($filename, $mimeType, $postFileName)
    {
        return function_exists('curl_file_create')
            ? curl_file_create($filename, $mimeType, $postFileName)
            : '@' . $filename . ';filename=' . $postFileName . ($mimeType ? ';type=' . $mimeType : '');
    }


    public function put()
    {
        $this->setPostParameters();
        return $this->withOption('CUSTOMREQUEST', 'PUT')->send();
    }


    public function patch()
    {
        $this->setPostParameters();
        return $this->withOption('CUSTOMREQUEST', 'PATCH')->send();
    }


    public function delete()
    {
        $this->setPostParameters();
        return $this->withOption('CUSTOMREQUEST', 'DELETE')->send();
    }


    public function head()
    {
        $this->appendDataToURL();

        $this->withCurlOption('NOBODY', true);
        $this->withCurlOption('HEADER', true);

        return $this->send();
    }


    protected function send()
    {
        if ($this->builderOptions['asJsonRequest']) {
            $this->withHeader('Content-Type: application/json');
        }

        if ($this->builderOptions['enableDebug']) {
            $debugFile = fopen($this->builderOptions['debugFile'], 'w');
            $this->withOption('STDERR', $debugFile);
        }

        $this->curlObject = curl_init();
        $options = $this->forgeOptions();

        curl_setopt_array($this->curlObject, $options);
        $response = curl_exec($this->curlObject);

        $responseHeader = null;

        if ($this->curlOptions['HEADER']) {
            $headerSize = curl_getinfo($this->curlObject, CURLINFO_HEADER_SIZE);
            $responseHeader = substr($response, 0, $headerSize);
            $response = substr($response, $headerSize);
        }

        $responseData = [];

        if ($this->builderOptions['responseObject'] || $this->builderOptions['responseArray']) {
            $responseData = curl_getinfo($this->curlObject);

            if (curl_errno($this->curlObject)) {
                $responseData['errorMessage'] = curl_error($this->curlObject);
            }
        }

        curl_close($this->curlObject);

        if ($this->builderOptions['saveFile']) {
            $file = fopen($this->builderOptions['saveFile'], 'w');
            fwrite($file, $response);
            fclose($file);
        } elseif ($this->builderOptions['asJsonResponse']) {
            $response = json_decode($response, $this->builderOptions['returnAsArray']);
        }

        if ($this->builderOptions['enableDebug']) {
            fclose($debugFile);
        }

        return $this->returnResponse($response, $responseData, $responseHeader);
    }


    protected function parseHeaders($headerString)
    {
        $headers = array_filter(array_map(function ($x) {
                $arr = array_map('trim', explode(':', $x, 2));

                if (count($arr) == 2) {
                    return [$arr[0] => $arr[1]];
                }
            },
            array_filter(array_map('trim', explode("\r\n", $headerString)))
        ));

        $results = [];

        foreach ($headers as $values) {
            if (! is_array($values)) {
                continue;
            }

            $key = array_keys($values)[0];

            if (isset($results[$key])) {
                $results[$key] = array_merge((array) $results[$key], [array_values($values)[0]]);
            } else {
                $results = array_merge($results, $values);
            }
        }

        return $results;
    }


    protected function returnResponse($content, array $responseData = [], $header = null)
    {
        if (! $this->builderOptions['responseObject'] && ! $this->builderOptions['responseArray']) {
            return $content;
        }

        $object = new \stdClass();
        $object->content = $content;
        $object->status = $responseData['http_code'];
        $object->contentType = $responseData['content_type'];

        if (array_key_exists('errorMessage', $responseData)) {
            $object->error = $responseData['errorMessage'];
        }

        if ($this->curlOptions['HEADER']) {
            $object->headers = $this->parseHeaders($header);
        }

        if ($this->builderOptions['responseObject']) {
            return $object;
        }

        if ($this->builderOptions['responseArray']) {
            return (array) $object;
        }

        return $content;
    }


    protected function forgeOptions()
    {
        $results = [];

        foreach ($this->curlOptions as $key => $value) {
            $arrayKey = constant('CURLOPT_' . $key);

            if (! $this->builderOptions['containsFile'] && $key === 'POSTFIELDS' && is_array($value)) {
                $results[$arrayKey] = http_build_query($value, null, '&');
            } else {
                $results[$arrayKey] = $value;
            }
        }

        if (! empty($this->builderOptions['xDebugSessionName'])) {
            $char = strpos($this->curlOptions['URL'], '?') ? '&' : '?';
            $this->curlOptions['URL'] .= $char . 'XDEBUG_SESSION_START=' . $this->builderOptions['xDebugSessionName'];
        }

        return $results;
    }


    protected function appendDataToURL()
    {
        $parameterString = '';

        if (is_array($this->builderOptions['data']) && count($this->builderOptions['data']) > 0) {
            $parameterString = '?' . http_build_query($this->builderOptions['data'], null, '&');
        }

        return $this->curlOptions['URL'] .= $parameterString;
    }
}