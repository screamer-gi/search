<?php
class SearchService
{
    /** @var AustraliaAdapter */
    private $adapter;

    public function __construct(AustraliaAdapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function doSearch(): SearchResult
    {
        $searchWord = $this->getSearchWord();
        $searchResult = $this->initSearch();
        var_dump($searchResult);die;
        $this->searchWord($searchResult, $searchWord);
        while ($this->searchNextPage($searchResult));
        return $searchResult;
    }

    public function initSearch(): SearchResult
    {
        $searchResult = new SearchResult();

        return $searchResult;
    }

    public function getSearchWord(): string
    {
        if (empty($_SERVER['argv'][1])) {
            throw new Exception('Search word required');
        }
        return $_SERVER['argv'][1];
    }

    public function searchWord(SearchResult $searchResult, string $searchWord)
    {
    }

    public function searchNextPage(SearchResult $searchResult):bool
    {
        return false;
    }
}