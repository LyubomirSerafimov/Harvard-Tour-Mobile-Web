<?php
/**
 * @package ExternalData
 */

/**
 * A generic class to handle the retrieval of external data
 * by URL
 * @package ExternalData
 */
 
class URLDataRetriever extends DataRetriever {

    protected $url;
    protected $baseURL;
    protected $method='GET';
    protected $filters=array();
    protected $requestHeaders=array();
    protected $streamContext = null;
    
    /**
     * Sets the base url for the request. This value will be set automatically if the BASE_URL argument
     * is included in the factory method. Subclasses that have fixed URLs (i.e. web service data controllers)
     * can set this in the init() method.
     * @param string $baseURL the base url including protocol
     * @param bool clearFilters whether or not to clear the filters when setting (default is true)
     */
    public function setBaseURL($baseURL, $clearFilters=true) {
        $this->baseURL = $baseURL;
        if ($clearFilters) {
            $this->removeAllFilters();
        }
    }
    
    /**
     * Adds a parameter to the url request. In the subclass has not overwritten url() then it will be added to the
     * url as a query string. Note that you can only have 1 value per parameter at this time. This method
     * will call clearInternalCache() since this will cause any previous data to be invalid.
     * @param string $var the parameter to add
     * @param mixed $value the value to assign. Must be a scalar value
     */
    public function addFilter($var, $value) {
        $this->filters[$var] = $value;
    }
    
    /**
     * Removes a parameter from the url request. This method will call clearInternalCache() since this 
     * will cause any previous data to be invalid.
     * @param string $var the parameter to remove
     */
    public function removeFilter($var) {
        if (isset($this->filters[$var])) {
            unset($this->filters[$var]);
        }
    }
    
    /**
     * Remove all parameters from the url request. This method will call clearInternalCache() since 
     * this will cause any previous data to be invalid.
     */
    public function removeAllFilters() {
        $this->filters = array();
    }
    
    public function init($args) {
        if (isset($args['BASE_URL'])) {
            $this->setBaseURL($args['BASE_URL']);
        }
        
        $this->initStreamContext($args);
    }

    public function addHeader($header, $value) {
        $this->requestHeaders[$header] = $value;
        $headers = array();
        //@TODO: Might need to escape this
        foreach ($this->requestHeaders as $header=>$value) {
            $headers[] = "$header: $value";
        }
            
        stream_context_set_option($this->streamContext, 'http', 'header', implode("\r\n", $headers));
    }
    
    public function getHeaders() {
        return $this->requestHeaders;
    }

    public function setMethod($method) {
        if (!in_array($method, array('POST','GET','DELETE','PUT'))) {
            throw new KurogoConfigurationException("Invalid method $method");
        }
        
        $this->method = $method;
        stream_context_set_option($this->streamContext, 'http', 'method', $method);
    }

    public function setTimeout($timeout) {
        stream_context_set_option($this->streamContext, 'http', 'timeout', $timeout);
    }
    
    protected function initStreamContext($args) {
        $streamContextOpts = array();
        
        if (isset($args['HTTP_PROXY_URL'])) {
            $streamContextOpts['http'] = array(
                'proxy'          => $args['HTTP_PROXY_URL'], 
                'request_fulluri'=> TRUE
            );
        }
        
        if (isset($args['HTTPS_PROXY_URL'])) {
            $streamContextOpts['https'] = array(
                'proxy'          => $proxyConfigs['HTTPS_PROXY_URL'], 
                'request_fulluri'=> TRUE
            );
        }
        
        $this->streamContext = stream_context_create($streamContextOpts);
    }
    
    
    /**
     * Returns the url to use for the request. The default implementation will take the base url and
     * append any filters/parameters as query string parameters. Subclasses can override this method 
     * if a more dynamic method of URL generation is needed.
     * @return string
     */
    protected function url() {
        $url = $this->baseURL;
        if (count($this->filters)>0) {
            $glue = strpos($this->baseURL, '?') !== false ? '&' : '?';
            $url .= $glue . http_build_query($this->filters);
        }
        
        return $url;
    }
    
    /**
     * Returns a base filename for the cache file that will be used. The default implementation uses
     * a hash of the value returned from the url
     * @return string
     */
    public function getCacheKey() {
        if (!$url = $this->url()) {
            throw new KurogoDataException("URL could not be determined");
        }
        return 'url_' . md5($url);
    }
    
    /**
     * Retrieves the data using the config url. The default implementation uses the file_get_content()
     * function to retrieve the request. Subclasses would need to implement this if a simple GET request
     * is not sufficient (i.e. you need POST or custom headers). 
     * @return string the response from the server
     * @TODO support POST requests and custom headers and perhaps proxy requests
     */
    public function retrieveData() {
        if (!$url = $this->url()) {
            throw new KurogoDataException("URL could not be determined");
        }

        $this->url = $url;
        
        Kurogo::log(LOG_INFO, "Retrieving $url", 'url_retriever');
        $data = file_get_contents($url, false, $this->streamContext);
        $http_response_header = isset($http_response_header) ? $http_response_header : array();

        $this->response = new URLDataResponse();
        $this->response->setRequest($this->method, $url, $this->filters, $this->requestHeaders);

        $this->response->setResponse($data, $http_response_header);
        
        Kurogo::log(LOG_DEBUG, sprintf("Returned status %d and %d bytes", $this->getResponseCode(), strlen($data)), 'url_retriever');
        
        return $data;
    }
}
