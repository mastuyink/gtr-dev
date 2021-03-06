<?php
namespace common\components;

use yii\base\Widget;
use yii\helpers\Html;
use common\models\THarbor;
use common\models\TKurs;
use frontend\models\BookForm;
use Yii;
use yii\helpers\ArrayHelper;

class BookingForm extends Widget
{
	const FASTBOAT = 1;
	const HOTELS   = 2; 
	public $formType = self::FASTBOAT;

    public function run()
    {
        parent::run();
        $this->renderView();
    }

    public function renderView(){
    	if ($this->formType === self::FASTBOAT) {
    		$session =Yii::$app->session;
			$modelBookForm = new BookForm();
			$session->open();
	        $listCurrency = ArrayHelper::map(TKurs::getCurrencyAsArray(), 'currency', 'currency_name','name');
	        $route = THarbor::getHarBorAsArray();
	        foreach ($route as $key => $value) {
	            $arrayRoute[] = ['id'=>$value['id'],'name'=>$value['name'],'island'=>$value['idIsland']['island']];
	        }
	         $listDept =ArrayHelper::map($arrayRoute, 'id', 'name', 'island');
	        $adultList = ['1'=>'1','2','3','4','5','6','7','8','9'];
	        $childList = ['0','1','2','3','4','5'];
	        echo $this->render('booking-form/_fastboats',[
					'modelBookForm' =>$modelBookForm,
					'listDept'      =>$listDept,
					'adultList'     =>$adultList,
					'childList'     =>$childList,
					'listCurrency'  =>$listCurrency,
					'session'       =>$session,
	        	]);
    	}elseif ($this->formType === self::HOTELS) {
    		echo $this->render('booking-form/_hotels');
    	}else{
    		echo "<h1 class='text-danger'>Form Type Not Found</h1>";
    	}
		

    }

   


}