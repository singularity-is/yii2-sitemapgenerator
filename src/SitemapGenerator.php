<?php
/**
 * Dositej Grbovic <info@singularity.is>
 * Company: Singularity <https://singularity.is>
 */


namespace singularity\sitemapgenerator;

use DateTime;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * Class SitemapGenerator
 *
 * @property \Icamys\SitemapGenerator\SitemapGenerator|object $generator
 */
class SitemapGenerator extends Model
{
    private $_generator;

    /**
     * @var $items array of items from which to generate urls
     *
     * Each item should be either string or array
     *
     * Items of array type should have the following structure:
     *
     * ```php
     * [
     *     'class' => 'myClass',
     *     'url' => 'myUrl'
     * ]
     * ```
     *
     * where
     *
     * - class: required, specifies the model class from which to request data.
     *   The class must extend from ActiveRecord.
     * - url: required|optional, specifies the url to use with each record from requested class data.
     *   You can pass as callback where the parameter is current record.
     *   The url is optional only if getPublicUrl() method is implemented in the class.
     *
     *
     * Below are some examples:
     *
     * as strings:
     * ```php
     * [
     *      'https://mysite/somepage',
     *      'https://mysite/somepage2',
     * ]
     * ```
     *
     * as arrays:
     * ```php
     * [
     *      [
     *          'class' => 'common\models\MyActiveRecordModel',
     *          'url' => function($model) {
     *              return ["mymodel/view/$model->id"];
     *          }
     *      ],
     *      ['class' => 'common\models\MyActiveRecordModelWithGetPublicUrl']
     * ]
     * ```
     */
    public $items = [];

    public $urlConfig = [];

    public function __construct($config = [])
    {
        $this->createGenerator($config);
        parent::__construct($config);
    }

    /**
     * @return int the number of generated urls
     */
    public function generate(): int
    {
        $this->addUrls($this->items);

        $this->generator
            ->createSitemap()
            ->writeSitemap()
            ->updateRobots()
            ->submitSitemap();

        return $this->generator->getURLsCount();
    }

    protected function addUrls($urls)
    {
        foreach ($urls as $config) {
            if (ArrayHelper::getValue($config, 'class')) {
                $this->addUrls($this->getUrlsFromClass($config));
            } else {
                $this->addUrl($config);
            }
        }
    }

    protected function addUrl($config)
    {
        if (is_string($config)) {
            $config = ['url' => $config];
        }

        $config = ArrayHelper::merge([
            'lastModified' => new DateTime(),
        ], $this->urlConfig, $config);

        $url = ArrayHelper::remove($config, 'url');

        if (is_array($url)) {
            $url = Url::to($url);
        }

        $this->generator->addURL(
            $url,
            ArrayHelper::remove($config, 'lastModified'),
            ArrayHelper::remove($config, 'changeFrequency'),
            ArrayHelper::remove($config, 'priority'),
            ArrayHelper::remove($config, 'alternatives')
        );
    }

    protected function getUrlsFromClass(array $config): array
    {
        $class = ArrayHelper::remove($config, 'class');

        if (!is_a($class, ActiveRecord::class, true)) {
            throw new Exception("Invalid class: $class. Must extend " . ActiveRecord::class . '.');
        }

        $urls = [];

        $globalUrl = ArrayHelper::remove($config, 'url');
        $records = $class::find()->all();
        foreach ($records as $record) {
            if (!$globalUrl) {
                if (!method_exists($record, 'getPublicUrl')) {
                    throw new InvalidConfigException("When setting item as array, you should provide url or implement getPublicUrl() method in the class.");
                }

                $url = $record->getPublicUrl();
            } else {
                $url = $globalUrl;
            }

            $urls[] = ArrayHelper::merge([
                'url' => is_callable($url) ? call_user_func($url, $record) : Url::to($url)
            ], $config);
        }

        return $urls;
    }

    protected function createGenerator($config)
    {
        $this->_generator = new \Icamys\SitemapGenerator\SitemapGenerator(
            ArrayHelper::getValue($config, 'baseUrl', Url::base(true)),
            ArrayHelper::getValue($config, 'basePath', ''),
            ArrayHelper::getValue($config, 'fs'),
            ArrayHelper::getValue($config, 'runtime')
        );
    }

    protected function getGenerator()
    {
        return $this->_generator;
    }

    /**
     * @param string $filename
     * @return $this
     */
    public function setSitemapFilename(string $filename = ''): SitemapGenerator
    {
        $this->generator->setSitemapFilename($filename);
        return $this;
    }

    /**
     * @param string $filename
     * @return $this
     */
    public function setSitemapIndexFilename(string $filename = ''): SitemapGenerator
    {
        $this->generator->setSitemapIndexFilename($filename);
        return $this;
    }

    /**
     * @param string $filename
     * @return $this
     */
    public function setRobotsFileName(string $filename): SitemapGenerator
    {
        $this->generator->setRobotsFileName($filename);
        return $this;
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setMaxURLsPerSitemap(int $value): SitemapGenerator
    {
        $this->generator->setMaxURLsPerSitemap($value);
        return $this;
    }
}