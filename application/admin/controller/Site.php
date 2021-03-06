<?php
// +----------------------------------------------------------------------
// | ShopXO 国内领先企业级B2C免费开源电商系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011~2019 http://shopxo.net All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Devil
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\service\ConfigService;
use app\service\GoodsService;
use app\service\BaseConfigHandleService;

/**
 * 站点设置
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class Site extends Common
{
	/**
	 * 构造方法
	 * @author   Devil
	 * @blog     http://gong.gg/
	 * @version  0.0.1
	 * @datetime 2016-12-03T12:39:08+0800
	 */
	public function __construct()
	{
		// 调用父类前置方法
		parent::__construct();

		// 登录校验
		$this->IsLogin();

		// 权限校验
		$this->IsPower();
	}

	/**
     * [Index 配置列表]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2016-12-06T21:31:53+0800
     */
	public function Index()
	{
		// 导航
        $nav_type = input('nav_type', 'base');
        $this->assign('nav_type', $nav_type);

		// 时区
		$this->assign('site_timezone_list', lang('site_timezone_list'));

		// 站点状态
		$this->assign('site_site_state_list', lang('site_site_state_list'));

		// 用户注册类型列表
		$this->assign('common_user_reg_state_list', lang('common_user_reg_state_list'));

		// 是否开启用户登录
		$this->assign('site_user_login_state_list', lang('site_user_login_state_list'));

		// 获取验证码-开启图片验证码
		$this->assign('site_img_verify_state_list', lang('site_img_verify_state_list'));

		// 图片验证码规则
		$this->assign('site_images_verify_rules_list', lang('site_images_verify_rules_list'));

		// 热门搜索关键字
		$this->assign('common_search_keywords_type_list', lang('common_search_keywords_type_list'));

		// 是否
		$this->assign('common_is_text_list', lang('common_is_text_list'));

		// 站点类型
		$this->assign('common_site_type_list', lang('common_site_type_list'));

		// 扣除库存规则
		$this->assign('common_deduction_inventory_rules_list', lang('common_deduction_inventory_rules_list'));

		// 首页商品排序规则
		$this->assign('goods_order_by_type_list', lang('goods_order_by_type_list'));
		$this->assign('goods_order_by_rule_list', lang('goods_order_by_rule_list'));

		// 配置信息
		$data = ConfigService::ConfigList();
		$this->assign('data', $data);

		// 数据处理
		switch($nav_type)
		{
			// 自提点
			case 'sitetype' :
				// 地址处理
	        	if(!empty($data['common_self_extraction_address']) && !empty($data['common_self_extraction_address']['value']))
				{
					$address = ConfigService::SiteTypeExtractionAddressList($data['common_self_extraction_address']['value']);
					$this->assign('sitetype_address_list', $address['data']);
				}

				// 加载百度地图api
	        	$this->assign('is_load_baidu_map_api', 1);
				break;

			// 网站设置
			case 'siteset' :
				// 获取商品一级分类
				$where = ['pid'=>0, 'is_home_recommended'=>1, 'is_enable'=>1];
            	$category = GoodsService::GoodsCategoryList(['where'=>$where]);
            	if(!empty($category))
            	{
            		$floor_keywords = (empty($data['home_index_floor_top_right_keywords']) || empty($data['home_index_floor_top_right_keywords']['value'])) ? [] : json_decode($data['home_index_floor_top_right_keywords']['value'], true);
            		foreach($category as &$c)
            		{
            			$c['config_keywords'] = isset($floor_keywords[$c['id']]) ? $floor_keywords[$c['id']] : '';
            		}
            	}
            	$this->assign('goods_category_list', $category);
				break;
		}

		// 编辑器文件存放地址
        $this->assign('editor_path_type', 'common');

        // 视图
        return $this->fetch($nav_type);
	}

	/**
	 * [Save 配置数据保存]
	 * @author   Devil
	 * @blog     http://gong.gg/
	 * @version  0.0.1
	 * @datetime 2017-01-02T23:08:19+0800
	 */
	public function Save()
	{
		// 参数
		$params = $_POST;

		// 导航
		$nav_type = input('nav_type', 'base');

		// 字段不存在赋空值
		$field_list = [];

		// 导航类型
		switch($nav_type)
		{
			// 用户注册
			case 'register' :
				$field_list[] = 'home_user_reg_state';
				$field_list[] = 'home_site_user_register_bg_images';
				break;

			// 用户登录
			case 'login' :
				$field_list[] = 'home_site_user_login_ad1_images';
				$field_list[] = 'home_site_user_login_ad2_images';
				$field_list[] = 'home_site_user_login_ad3_images';
				break;

			// 密码找回
			case 'forgetpwd' :
				$field_list[] = 'home_site_user_forgetpwd_ad1_images';
				$field_list[] = 'home_site_user_forgetpwd_ad2_images';
				$field_list[] = 'home_site_user_forgetpwd_ad3_images';
				break;

			// 图片验证码
			case 'verify' :
				$field_list[] = 'common_images_verify_rules';
				break;

			// 站点类型
			case 'sitetype' :
				// 自提地址处理
				if(!empty($params['common_self_extraction_address']))
				{
					if(!is_array($params['common_self_extraction_address']))
					{
						$address = json_decode($params['common_self_extraction_address'], true);
					} else {
						$address = $params['common_self_extraction_address'];
					}
					foreach($address as $k=>$v)
					{
						$address[$k]['id'] = $k;
					}
					$params['common_self_extraction_address'] = json_encode($address, JSON_UNESCAPED_UNICODE);
				}
				break;

			// 网站设置
			case 'siteset' :
				$params['home_index_floor_top_right_keywords'] = empty($params['home_index_floor_top_right_keywords']) ? '' : json_encode($params['home_index_floor_top_right_keywords'], JSON_UNESCAPED_UNICODE);
				break;

			// 缓存
			case 'cache' :
				// session是否使用缓存
				if(isset($params['common_session_is_use_cache']) && $params['common_session_is_use_cache'] == 1)
				{
					// 连接测试
					$ret = $this->RedisCheckConnectPing($params['common_cache_session_redis_host'], $params['common_cache_session_redis_port'], $params['common_cache_session_redis_password']);
					if($ret['code'] != 0)
					{
						return $ret;
					}
				}

				// 数据是否使用缓存
				if(isset($params['common_data_is_use_cache']) && $params['common_data_is_use_cache'] == 1)
				{
					// 连接测试
					$ret = $this->RedisCheckConnectPing($params['common_cache_data_redis_host'], $params['common_cache_data_redis_port'], $params['common_cache_data_redis_password']);
					if($ret['code'] != 0)
					{
						return $ret;
					}
				}
				break;
		}

		// 开始处理空值
		if(!empty($field_list))
		{
			foreach($field_list as $field)
			{
				if(!isset($params[$field]))
				{
					$params[$field] = '';
				}
			}
		}

		// 基础配置
		$ret = ConfigService::ConfigSave($params);

		// 清除缓存
		if($ret['code'] == 0)
		{
			switch($nav_type)
			{
				// 登录
				case 'login' :
					cache(config('shopxo.cache_user_login_left_key'), null);

				// 密码找回
				case 'forgetpwd' :
					cache(config('shopxo.cache_user_forgetpwd_left_key'), null);
					break;

				// 缓存
				case 'cache' :
					$res = BaseConfigHandleService::Run();
					if($res['code'] != 0)
					{
						return $res;
					}
					break;
			}
		}

		return $ret;
	}

	/**
	 * redis连接测试
	 * @author  Devil
	 * @blog    http://gong.gg/
	 * @version 1.0.0
	 * @date    2020-09-26
	 * @desc    description
	 * @param   [string]        $host     [连接地址]
	 * @param   [int]           $port     [端口]
	 * @param   [string]        $password [密码]
	 */
	private function RedisCheckConnectPing($host, $port, $password)
	{
		// 参数处理
		$host = empty($host) ? '127.0.0.1' : $host;
		$port = empty($port) ? 6379 : $port;
		$password = empty($password) ? '' : $password;

		// 是否已安装redis扩展
		if(!extension_loaded('redis'))
		{
			return DataReturn('请先安装redis扩展', -1);
		}

		// 捕获异常
		try {
			// 连接redis
			$redis = new \Redis();
			$redis->connect($host, $port);
			if($password != '')
			{
	            $redis->auth($password);
	        }
        } catch(\Exception $e) {
		    return DataReturn('redis连接失败['.$e->getMessage().']', -1);
		}

		// 检测是否连接成功
		if($redis->ping())
		{
			return DataReturn('redis连接成功', 0);
		}
		return DataReturn('redis连接失败', -1);
	}
}
?>