<?php

namespace app\models;

use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use Yii;

class Task extends BaseModel
{
    const STATUS_PENDING = 1;
    const STATUS_ACTIVE = 2;
    const PRIORITY_LOW = 1;
    const PRIORITY_MEDIUM = 2;
    const PRIORITY_HIGH = 3;

    const CACHE_PREFIX_TASK_ALL = 'task_all';
    const CACHE_TAG_DEPENDENCY = 'task_collection';

    const STATUS_LIST = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_ACTIVE => 'Active',
    ];

    const PRIORITY_LIST = [
        self::PRIORITY_LOW => 'Low',
        self::PRIORITY_MEDIUM => 'Medium',
        self::PRIORITY_HIGH => 'High',
    ];

    public static function tableName(): string
    {
        return '{{%tasks}}';
    }

    public function rules(): array
    {
        return [
            [['title', 'due_date', 'status', 'priority'], 'required'],
            [['description'], 'string'],
            [['due_date'], 'safe'],
            [['status'], 'in', 'range' => array_keys(self::STATUS_LIST)],
            [['priority'], 'in', 'range' => array_keys(self::PRIORITY_LIST)],
            [['title'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'description' => 'Description',
            'due_date' => 'Due Date',
            'status' => 'Status',
            'priority' => 'Priority',
        ];
    }

    // Setter for the 'due_date' attribute
    public function setDueDate($value)
    {
        $value = \Yii::$app->formatter->asDate($value, 'php:Y-m-d');

        // Store the due date in date format
        $this->setAttribute('due_date', strtolower($value));
    }

    /**
     * @return mixed|ActiveQuery
     * @throws \Throwable
     */
    public static function findTasks()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => self::find(),
            'pagination' => [
                'pageSize' => 2
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ],
        ]);

        return Yii::$app->cache->getOrSet(self::CACHE_PREFIX_TASK_ALL , function () use ($dataProvider) {
            $dataProvider->prepare();
            return $dataProvider;
        }, 3600, static::generateTagDependency(self::CACHE_TAG_DEPENDENCY));
    }
}
