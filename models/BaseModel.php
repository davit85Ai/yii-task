<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\caching\TagDependency;
use yii\db\ActiveRecord;

class BaseModel extends ActiveRecord
{
    /**
     * @return array[]
     */
    public function behaviors(): array
    {
        return [
            TimestampBehavior::class
        ];
    }

    /**
     * @param string $key
     * @return TagDependency
     */
    public static function generateTagDependency(string $key): TagDependency
    {
        return new TagDependency(['tags' => $key]);
    }

    /**
     * @param string $key
     * @return void
     */
    public static function resetCache(string $key): void
    {
        TagDependency::invalidate(\Yii::$app->cache, $key);
    }
}
