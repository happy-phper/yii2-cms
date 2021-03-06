<?php
namespace core\modules\admin\controllers;

use core\modules\admin\models\Admin;
use core\modules\home\models\Contact;
use core\modules\news\models\News;
use core\modules\products\models\Products;
use Yii;
use core\modules\admin\components\Controller;
use core\modules\admin\forms\LoginForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class IndexController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = [
            'access' => [
                'class' => AccessControl::class,
                'user' => 'admin',
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => [],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],

            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];

        return ArrayHelper::merge($behaviors, parent::behaviors());
    }

    /**
     * Home Index
     */
    public function actionIndex()
    {
        $news_count = News::find()->count();
        $product_count = Products::find()->count();
        $contact_count = Contact::find()->count();
        $manager_user_count = Admin::find()->count();

        return $this->render(
            'index',
            compact('news_count', 'product_count', 'contact_count', 'manager_user_count')
        );
    }

    /**
     * User login
     */
    public function actionLogin()
    {
        $this->layout = 'main-login';
        if (!Yii::$app->admin->isGuest) {
            return $this->redirect(['/admin']);
        }
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            $returnUrl = Yii::$app->admin->returnUrl;
            if ($returnUrl == '/index.php') {
                $returnUrl = Url::to(['/admin']);
            }
            return $this->redirect($returnUrl);
        } else {
            return $this->render('login', [
                'model' => $model
            ]);
        }
    }

    /**
     * User logout
     */
    public function actionLogout()
    {
        Yii::$app->admin->logout();
        return $this->redirect(['login']);
    }

    /**
     * Ajax setting
     */
    public function actionAjaxLayoutSetting()
    {
        $params = Yii::$app->request->post();
        if (isset($params['name']) && isset($params['value'])) {
            $this->module->settings->set($params['name'], $params['value']);
        }
    }

    /**
     * Set language
     */
    public function actionSetLanguage()
    {
        return $this->render('set-language');
    }
}
