<?php
class SearchResult
{
    /** @var array */
    protected $result = [];

    /** @var array */
    protected $cookie = [];

    /** @var string */
    protected $csrfValue;

    protected $currentPage;

    protected $totalPages;

    protected $totalResults;

    public function setCookie(array $cookie)
    {
        $this->cookie = array_merge($this->cookie, $cookie);
    }

    public function asJson(): string
    {
        return json_encode($this->result);
    }
}