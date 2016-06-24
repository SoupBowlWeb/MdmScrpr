<?php namespace MdmScrpr;

use Symfony\Component\DomCrawler\Crawler;

class MdmScrpr
{
    protected static $mediumUrl = 'https://medium.com';
    protected $publication;
    protected $crawler;
    protected $scrapedHtml;

    public function __construct($publication)
    {
        $this->publication = $publication;
        $this->crawler = new Crawler('', static::$mediumUrl);
    }

    public function scrape()
    {
        $this->scrapedHtml = file_get_contents($this->getPublicationUrl());
        $this->crawler->addHtmlContent($this->scrapedHtml);

        return $this->crawler->filter('.block--preview')->each(function (Crawler $node, $i) {
            return (object)[
                'title' => $this->getTitle($node),
                'author' => $this->getAuthor($node),
                'publish_date' => $this->getPublishDate($node),
                'snippet' => $this->getSnippet($node),
                'url' => $this->getPostUrl($node),
                'author_avatar' => $this->getAvatar($node)
            ];
        });
    }

    private function getTitle($node)
    {
        try {
            return $node->filter('.block-streamText .postArticle .section-inner h3')->text();
        } catch (\Exception $e) {
            return $node->filter('.block-streamText .postArticle .section-inner h2')->text();
        }
    }

    private function getAuthor($node)
    {
        return $node->filter('.postMetaInline-authorLockup > a')->text();
    }

    private function getPublishDate($node)
    {
        $date = $node->filter('.postMetaInline-authorLockup > div > a')->text();

        return date("F j, Y", strtotime($date));
    }

    private function getSnippet($node)
    {
        try {
            return $node->filter('.block-streamText .postArticle .section-inner p')->text();    
        } catch (\Exception $e) {
            return $node->filter('.block-streamText .postArticle .section-inner h4[name=previewSubtitle]')->text();
        }
    }

    private function getAvatar($node)
    {
        return $node->filter('img.avatar-image')->first()->image()->getUri();
    }

    public function getPostUrl($node)
    {
        $url = $node->filter('.block-streamText .postArticle > a')->link()->getUri();

        return strtok($url, '?');
    }

    private function getPublicationUrl()
    {
        return static::$mediumUrl . '/' . $this->publication;
    }

}
