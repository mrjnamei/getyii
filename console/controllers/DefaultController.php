<?php

namespace console\controllers;

use frontend\modules\topic\models\Topic;
use frontend\modules\user\models\UserMeta;
use Yii;
use common\models\PostComment;
use common\models\UserInfo;
use yii\console\Controller;
use yii\db\Expression;


class DefaultController extends Controller
{
    public function actionSync()
    {
        UserInfo::updateAll(['thanks_count' => 0, 'like_count' => 0, 'hate_count' => 0]);
        $meta = UserMeta::find()->all();
        foreach ($meta as $key => $value) {
            if (in_array($value->type, ['thanks', 'like', 'hate'])) {
                switch ($value->target_type) {
                    case 'topic':
                    case 'post':
                        $this->stdout("同步文章操作……\n");
                        $topic = Topic::findOne($value->target_id);
                        if (UserInfo::updateAllCounters([$value->type . '_count' => 1], ['user_id' => $topic->user_id])) {
                            $this->stdout("同步评论成功`(*∩_∩*)′\n");
                        } else {
                            $this->stdout("同步评论失败::>_<::\n");
                        }
                        break;

                    case 'comment':
                        $this->stdout("同步评论操作……\n");
                        $comment = PostComment::findOne($value->target_id);
                        if (UserInfo::updateAllCounters([$value->type . '_count' => 1], ['user_id' => $comment->user_id])) {
                            $this->stdout("同步评论成功`(*∩_∩*)′\n");
                        } else {
                            $this->stdout("同步评论失败::>_<:: \n");
                        }
                        break;

                    default:
                        # code...
                        break;
                }
            }
        }
        return;
    }

    public function actionPostLastCommonTime()
    {
        $update = Topic::updateAll(['last_comment_time' => new Expression('created_at')], ['type' => Topic::TYPE]);
        $this->stdout("同步最后回复时间，同步{$update}条数据\n");
    }
}
