<?php

namespace app\api\controller;

use think\Controller;
use think\facade\Session;

use think\facade\Config;
use think\facade\Request;
//use think\Request;

class Index extends Controller
{

    //首页数据
    public function main()
    {
        //订单
        $star_time = date("Y-m-d H:i:s", mktime(0, 0, 0, date('m'), date('d'), date('Y')));
        $end_time = date("Y-m-d H:i:s", mktime(0, 0, 0, date('m'), date('d')+1, date('Y'))-1);
        $order=db('order')->where('start_time','>',strtotime($star_time))->where('start_time','<',strtotime($end_time))->count();//今日订单总数
//        halt($order);
        $moneystart=db('order')->where('start_time','>',strtotime($star_time))->where('start_time','<',strtotime($end_time))->where('is_pay',1)->sum('pay_price');//今日未支付总额
        $moneyend=db('order')->where('start_time','>',strtotime($star_time))->where('start_time','<',strtotime($end_time))->where('is_pay',2)->sum('pay_price');//今日已支付总额
        //用户和商家
        $user=db('user')->count();//用户总数
        $userpt=db('user')->where(array('is_proxy'=>1))->count();//普通用户总数
        $userdl=db('user')->where(array('is_proxy'=>2))->count();//代理商总数
        //车辆
        $car=db('car')->count();//车辆总数
        $malfunction=db('malfunction')->where(array('state'=>2))->count();//车辆报修已处理总数
        $malfunctions=db('malfunction')->where(array('state'=>0))->count();//车辆报修未处理总数
        //注册用户
        $sdefaultDate = date("Y-m-d");
//$first =1 表示每周星期一为开始日期 0表示每周日为开始日期

        $first=1;
//获取当前周的第几天 周日是 0 周一到周六是 1 - 6
        $w=date('w',strtotime($sdefaultDate));
//获取本周开始日期，如果$w是0，则表示周日，减去 6 天
        $week_start=date('Y-m-d',strtotime("$sdefaultDate -".($w ? $w - $first : 6).' days'));
//本周结束日期
        $week_end=date('Y-m-d',strtotime("$week_start +6 days"));
        //  查询本周注册用户
        $user1=db('user')->where('addtime','>',strtotime($week_start))->where('addtime','<',strtotime("+1 day",strtotime($week_start)))->count();//周一注册总数
        $user2=db('user')->where('addtime','>',strtotime("+1 day",strtotime($week_start)))->where('addtime','<',strtotime("+2 day",strtotime($week_start)))->count();//周一注册总数
        $user3=db('user')->where('addtime','>',strtotime("+2 day",strtotime($week_start)))->where('addtime','<',strtotime("+3 day",strtotime($week_start)))->count();//周一注册总数
        $user4=db('user')->where('addtime','>',strtotime("+3 day",strtotime($week_start)))->where('addtime','<',strtotime("+4 day",strtotime($week_start)))->count();//周一注册总数
        $user5=db('user')->where('addtime','>',strtotime("+4 day",strtotime($week_start)))->where('addtime','<',strtotime("+5 day",strtotime($week_start)))->count();//周一注册总数
        $user6=db('user')->where('addtime','>',strtotime("+5 day",strtotime($week_start)))->where('addtime','<',strtotime($week_end))->count();//周一注册总数
        $user7=db('user')->where('addtime','>',strtotime($week_end))->where('addtime','<',strtotime("+1 day",strtotime($week_end)))->count();//周日注册总数
        $cont = [$user1,$user2,$user3,$user4,$user5,$user6,$user7];
        //交易记录
        $arr = array();
        $array =array();
        $currentTime = time();
        $cyear = floor(date("Y",$currentTime));
        $cMonth = floor(date("m",$currentTime));
        for($i=0;$i<12;$i++) {
            $nMonth = $cMonth - $i;
            $cyear = $nMonth == 0 ? ($cyear - 1) : $cyear;
            $nMonth = $nMonth <= 0 ? 12 + $nMonth : $nMonth;
            $date = $cyear . "-" . $nMonth . "-1";
            $firstday = date('Y-m-01', strtotime($date));
            $lastday = date('Y-m-t', strtotime($date));
            $orders=db('order')->where('start_time','>',strtotime($firstday))->where('start_time','<',strtotime($lastday))->count();//今日订单总数
            $arr[$i]=$orders;
            $array[$i]=date('Y_m',strtotime($firstday));
        }
        //$fortime = Request::param('2019-10-15');//time

        if(!empty($fortime)){
            $star_time=strtotime(date('Y-m-d 00:00:00',strtotime($fortime)));
            $end_time=strtotime(date('Y-m-d 23:59:59',strtotime($fortime)));
            $time = date('Y-m-d H:i:s',$end_time);
        }else{
            $star_time=strtotime(date('Y-m-d 00:00:00'));
            $end_time=strtotime(date('Y-m-d 23:59:59'));
            $time = date('Y-m-d H:i:s',time());
        }
        //微信登陆数
        $user_card_sum=db('user')->where('id_card',null)->count();//认证人数
        $user_phgone_sum=db('user')->where('is_phone',2)->count();//手机注册人数
        $user_vx_sum=db('user')->where('wechat_open_id','neq',0)->count();//微信人数
        $user_vx_sum_zong=db('user')->count();//总人数
        $user_price_sum=db('card')->where('end_time','<',time())->where('is_expired',0)->count();//免押金数
        //chongzhi shu
        $user_order_sum=db('deposit_order')->where('pay_time','between',[$star_time,$end_time])
            ->count();//免押金数
        $arr_arra=array(
            'user_card_sum'=>$user_card_sum,
            'user_phgone_sum'=>$user_phgone_sum,
            'user_vx_sum'=>$user_vx_sum,
            'user_vx_sum_zong'=>$user_vx_sum_zong,
            'user_price_sum'=>$user_price_sum,
            'user_order_sum'=>$user_order_sum,
        );
        //充值金额
        //$star_time = strtotime(date("Y-m-d",time()));//今天0点的时间点
        //$end_time = $star_time + 3600 * 24 -1;
        $price = db('recharge')->where('time','between',[$star_time,$end_time])->field('pay_price')->sum('pay_price');
        //今日 免押金金额
        $card = db('card_order')->where('pay_time','between',[$star_time,$end_time])->field('money')->sum('money');
        $sum = $price + $card;//免押金卡和充值余额

        //免押金卡总金额
        //$change = db('card_order')->field('money')->sum('money');
        //充值总金额
        //$sumprice = db('recharge')->field('pay_price')->sum('pay_price');
        //今日总收益
        $revens = $card + $price;
        //今日欠款总金额
        $owed = db('arrears')->where('money','between',[$star_time,$end_time])->field('money')->sum('money');
        //今日欠款回收金额
        $owedh = $revens - $owed;

        //有收益订单
        $charorder = db('order')->where('pay_price','gt','0')->count();

        //未产生收益订单
        $noworder = db('order')->where('pay_price','<=','0')->count();

        //订单均价
        $aveorder = $charorder==0?0:round($revens / $charorder,2);
        //订单/车的比例
        $ordercar = db('order')->count();//订单总数
        $cars = db('car')->count();//车
        $order_car = $ordercar / $cars;
        $ordercars = ceil($order_car);


        $arr = json_encode(array_reverse($arr));
        $array = json_encode(array_reverse($array));
        $cont = json_encode($cont);
        $data = $this->index_car_list();


        return json(['sum'=>$sum,//总金额
                     'change'=>$card,//今日免押卡总金额
                     'prices'=>$price,//今日充值总金额
                     'revens'=>$revens,//今日总收益
                     'owed'=>$owed,//欠款总金额
                     'owedh'=>$owedh,//欠款回收总金额

                     'charorder'=>$charorder,//产生收益的订单
                     'noworder'=>$noworder,//免费订单
                     'aveorder'=>$aveorder,//订单均价
                     'ordercars'=>$ordercars,//订单/车的比例
                     'time'=>$time,//当前时间
                     'arr_arra'=>$arr_arra,
                     'cont'=>$arr,
                     'conts'=>$array,
                     'connt'=>$cont,
                     'orderconnt'=>$order,
                     'moneystart'=>$moneystart,
                     'moneyend'=>$moneyend,
                     'userconnt'=>$user,
                     'userpt'=>$userpt,
                     'userdl'=>$userdl,
                     'car'=>$car,
                     'malfunction'=>$malfunction,
                     'malfunctions'=>$malfunctions,
                     'list'=>$data
                    ]);
    }
    //车辆统计详情
    public function index_car_list(){
        //车辆

        $car=db('car')->count();//车辆总数
        $car_line=db('car')->where('is_login',1)->count();//车辆在线总数
        $car_lines=round($car_line/$car*100,2).'％';//车辆在线率
        $car_fault=db('car')->where('type',6)->count();//车辆故障总数
        $car_faults=round($car_fault/$car*100,2).'％';//车辆故障率
        $car_precipitate=db('car')->where('is_login',1)->where('precipitation_time','>=',360)->where('precipitation_time','<',720)->count();//车辆一级沉淀总数
        $car_precipitates=round($car_precipitate/$car*100,2).'％';//车辆一级沉淀率
        $car_precipitatea=db('car')->where('is_login',1)->where('precipitation_time','>=',720)->count();//车辆二级沉淀总数
        $car_precipitatesa=round($car_precipitatea/$car*100,2).'％';//车辆二级沉淀率

        $car_quantity=db('car')->where('is_login',1)->where('electricity','<',20)->count();//车辆电量低于20%总数
        $car_quantitys=round($car_quantity/$car*100,2).'％';//车辆电量低于20%率
        $car_quantitya=db('car')->where('is_login',1)->where('electricity','<',40)->count();//车辆电量低于40%总数
        $car_quantitysa=round($car_quantitya/$car*100,2).'％';//车辆电量低于40%率

        $data = array(
            'car'=>$car.'辆',
            'car_line'=>$car_line.'辆',
            'car_lines'=>$car_lines,
            'car_fault'=>$car_fault.'辆',
            'car_faults'=>$car_faults,

            'car_precipitate'=>$car_precipitate.'辆',
            'car_precipitates'=>$car_precipitates,
            'car_precipitatea'=>$car_precipitatea.'辆',
            'car_precipitatesa'=>$car_precipitatesa,

            'car_quantity'=>$car_quantity.'辆',
            'car_quantitys'=>$car_quantitys,
            'car_quantitya'=>$car_quantitya.'辆',
            'car_quantitysa'=>$car_quantitysa,
        );
        return $data;

    }
}