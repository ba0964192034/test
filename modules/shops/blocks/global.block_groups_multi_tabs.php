<?php

/**
 * @Project NUKEVIET 4.x
 * @Author KENNYNGUYEN (nguyentiendat713@gmail.com)
 * @License GNU/GPL version 2 or any later version
 * @Createdate Sat, 03 Oct 2015 06:46:54 GMT
 */

if( ! defined( 'NV_MAINFILE' ) ) die( 'Stop!!!' );

if( ! nv_function_exists( 'nv_block_news_groups_multi_tabs' ) )
{
	
	function nv_block_config_news_groups_multi_tabs( $module, $data_block, $lang_block )
	{
		global $db_config,$nv_Cache, $site_mods;

		$html = '<tr>';
		$html .= '<td>' . $lang_block['blockid'] . '</td>';
		$sql = "SELECT bid, " . NV_LANG_DATA . "_title," . NV_LANG_DATA . "_alias FROM " . $db_config['prefix'] . "_" . $site_mods[$module]['module_data'] . "_block_cat ORDER BY weight ASC";
			$list = $nv_Cache->db( $sql, 'catid', $module );
		$html .= '<td>';
		foreach( $list as $l )
		{
			$xtitle_i = '';

			if( $l['lev'] > 0 )
			{
				for( $i = 1; $i <= $l['lev']; ++$i )
				{
					$xtitle_i .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				}
			}
			$html .= $xtitle_i . '<label><input type="checkbox" name="config_blockid[]" value="' . $l['bid'] . '" ' . ( ( in_array( $l['bid'], $data_block['blockid'] ) ) ? ' checked="checked"' : '' ) . '</input>' . $l[NV_LANG_DATA . '_title']  . '</label><br />';
		}
		$html .= '</td>';
		$html .= '</tr>';
		
		$html .= '<tr>';
		$html .= '<td>' . $lang_block['numrow'] . '</td>';
		$html .= '<td><input type="text" class="form-control w200" name="config_numrow" size="5" value="' . $data_block['numrow'] . '"/></td>';
		$html .= '</tr>';
		
		$html .= '<tr>';
		$html .= '<td>' . $lang_block['title_length'] . '</td>';
		$html .= '<td><input type="text" class="form-control w200" name="config_title_length" size="5" value="' . $data_block['title_length'] . '"/></td>';
		$html .= '</tr>';
		return $html;
	}

	function nv_block_config_news_groups_multi_tabs_submit( $module, $lang_block )
	{
		global $nv_Request;
		$return = array();
		$return['error'] = array();
		$return['config'] = array();
		$return['config']['blockid'] =$nv_Request->get_array( 'config_blockid', 'post', array() );
		$return['config']['numrow'] = $nv_Request->get_int( 'config_numrow', 'post', 0 );
		$return['config']['title_length'] = $nv_Request->get_string( 'config_title_length', 'post', 0 );
		return $return;
	}

	 if (! nv_function_exists('nv_get_price_tmp')) {

        function nv_get_price_tmp($module_name, $module_data, $module_file, $pro_id)
        {
            global $nv_Cache, $db, $db_config, $module_config, $discounts_config;

            $price = array();
            $pro_config = $module_config[$module_name];

            require_once NV_ROOTDIR . '/modules/' . $module_file . '/site.functions.php';
            $price = nv_get_price($pro_id, $pro_config['money_unit'], 1, false, $module_name);

            return $price;
        }
    }


	function nv_block_news_groups_multi_tabs( $block_config )
	{
	global $site_mods, $global_config,$nv_Cache, $lang_module, $module_config, $module_config, $module_name, $module_info, $global_array_shops_cat, $db_config, $my_head, $db, $pro_config, $money_config;

		$module = $block_config['module'];
		$mod_data = $site_mods[$module]['module_data'];
		$mod_file = $site_mods[$module]['module_file'];
		$pro_config = $module_config[$module];
		$sql = 'SELECT catid, parentid, lev, ' . NV_LANG_DATA . '_title AS title, ' . NV_LANG_DATA . '_alias AS alias, viewcat, numsubcat, subcatid, numlinks, ' . NV_LANG_DATA . '_description AS description, inhome, ' . NV_LANG_DATA . '_keywords AS keywords, groups_view, typeprice FROM ' . $db_config['prefix'] . '_' . $mod_data . '_catalogs ORDER BY sort ASC';

			foreach( $list as $row )
			{
				$global_array_shops_cat[$row['catid']] = array(
					'catid' => $row['catid'],
					'parentid' => $row['parentid'],
					'title' => $row['title'],
					'alias' => $row['alias'],
					'link' => NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module . '&amp;' . NV_OP_VARIABLE . '=' . $row['alias'],
					'viewcat' => $row['viewcat'],
					'numsubcat' => $row['numsubcat'],
					'subcatid' => $row['subcatid'],
					'numlinks' => $row['numlinks'],
					'description' => strip_tags($row['description']),
					'inhome' => $row['inhome'],
					'keywords' => $row['keywords'],
					'groups_view' => $row['groups_view'],
					'lev' => $row['lev'],
					'typeprice' => $row['typeprice']
				);
			}
			unset( $list,$l );
		
		
			// Lay ty gia ngoai te
			$sql = 'SELECT code, currency, exchange, round, number_format FROM ' . $db_config['prefix'] . '_' . $mod_data . '_money_' . NV_LANG_DATA;
			$cache_file = NV_LANG_DATA . '_' . md5( $sql ) . '_' . NV_CACHE_PREFIX . '.cache';
			
			
			if( ($cache = $nv_Cache->getItem( $module, $cache_file )) != false )
			{
				$money_config = unserialize( $cache );
			}
			else
			{
				$money_config = array();
				$result = $db->query( $sql );
				
				while( $row = $result->fetch() )
				{
					$money_config[$row['code']] = array(
						'code' => $row['code'],
						'currency' => $row['currency'],
						'exchange' => $row['exchange'],
						'round' => $row['round'],
						'number_format' => $row['number_format'],
						'decimals' => $row['round'] > 1 ? $row['round'] : strlen( $row['round'] ) - 2,
						'is_config' => ($row['code'] == $pro_config['money_unit']) ? 1 : 0
					);
				}
				$result->closeCursor();
				$cache = serialize( $money_config );
				$nv_Cache->setItem( $module, $cache_file, $cache );
			}
		
		
		if( empty( $block_config['blockid'] ) ) return '';

		$blockid = implode(',',$block_config['blockid']);
		
		if( file_exists( NV_ROOTDIR . '/themes/' . $global_config['module_theme'] . '/modules/shops/block_groups_multi_tabs.tpl' ) )
		{
			$block_theme = $global_config['module_theme'];
		}
		else
		{
			$block_theme = 'default';
		}
		$xtpl = new XTemplate( 'block_groups_multi_tabs.tpl', NV_ROOTDIR . '/themes/' . $block_theme . '/modules/shops' );
		
		$n = 0;
		
		$sql = 'SELECT bid ,vi_title FROM ' . $db_config['prefix'] . '_' . $mod_data . '_block_cat WHERE bid IN ( '.$blockid.' ) ORDER BY weight ASC' ;
		$result = $db->query( $sql );
		
		while( $data = $result->fetch( ) )
		{
			$n++;
			if($n==1)
			{
				$data['active'] = 'active';
			}

			$xtpl->assign( 'BLOCK_INFO', $data);
			$xtpl->parse( 'main.group_info' );
			
			$db->sqlreset()
				->select( 't1.id, t1.listcatid, t1.' . NV_LANG_DATA . '_title AS title, t1.' . NV_LANG_DATA . '_alias AS alias, t1.' . NV_LANG_DATA . '_hometext AS hometext, t1.homeimgfile, t1.homeimgthumb , t1.homeimgalt,t1.product_number, t1.product_price, t1.money_unit, t1.discount_id, t1.showprice ' )
				->from( '' . $db_config['prefix'] . '_' . $mod_data . '_rows t1' )
				->join( 'INNER JOIN ' . $db_config['prefix'] . '_' . $mod_data . '_block t2 ON t1.id = t2.id' )
				->where( 't2.bid= ' . $data['bid'] . ' AND t1.status= 1' )
				->order( 't2.weight ASC' )
				->limit( $block_config['numrow'] );
			$list = $nv_Cache->db( $db->sql(), 'id', $module );
	
			if( ! empty( $list ) )
			{	
				foreach( $list as $l )
				{
					
		
					$l['link'] = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module . '&amp;' . NV_OP_VARIABLE . '='. $global_array_shops_cat[$l['listcatid']]['alias'] . '/' . $l['alias'] . $global_config['rewrite_exturl'];
					if( $l['homeimgthumb'] == 1 )
					{
						$l['thumb'] = NV_BASE_SITEURL . NV_UPLOADS_DIR . '/' . $site_mods[$module]['module_upload'] . '/' . $l['homeimgfile'];
					}
					elseif( $l['homeimgthumb'] == 2 )
					{
						$l['thumb'] = NV_BASE_SITEURL . NV_UPLOADS_DIR . '/' . $site_mods[$module]['module_upload'] . '/' . $l['homeimgfile'];
					}
					elseif( $l['homeimgthumb'] == 3 )
					{
						$l['thumb'] = $l['homeimgfile'];
					}
					elseif( ! empty( $show_no_image ) )
					{
						$l['thumb'] = NV_BASE_SITEURL . $show_no_image;
					}
					else
					{
						$l['thumb'] = '';
					}
					$l['bid'] = $data['bid'];
					$xtpl->assign( 'id', $l['id'] );
					
					$l['title'] = nv_clean60( $l['title'], $block_config['title_length'] );
					
					// San pham yeu thich
					if( $pro_config['active_wishlist'] )
					{
						if( !empty( $array_wishlist_id ) )
						{
							if( in_array( $l['id'], $array_wishlist_id ) )
							{
								$xtpl->parse( 'main.group_content.loop.wishlist.disabled' );
							}
						}
						$xtpl->parse( 'main.group_content.loop.wishlist' );
					}
//print_r($pro_config['active_price']);die;
								if( $pro_config['active_price'] == '1' )
									{
										if( $l['showprice'] == '1' )
										{
											$price = nv_get_price_tmp( $module, $mod_data, $mod_file, $l['id'] );
											//var_dump($price); die;
											
											$xtpl->assign( 'PRICE', $price );
											if( $l['discount_id'] and $price['discount_percent'] > 0 )
											{
												$xtpl->parse( 'main.group_content.loop.price.discounts' );
											}
											else
											{
												
												if(  $l['product_price'] < '2' )
											{
												$xtpl->parse( 'main.group_content.loop.price.no_dong' );
											}
											else
											{
												$xtpl->parse( 'main.group_content.loop.price.no_discounts' );
											}
									
											}
											$xtpl->parse( 'main.group_content.loop.price' );
										}
										else
										{
											$xtpl->parse( 'main.group_content.loop.contact' );
										}
									}
					$l['hometext'] =  strip_tags($l['hometext']);				
					$l['hometext'] = nv_clean60($l['hometext'], 200);
				
					$xtpl->assign( 'ROW', $l );
					if( ! empty( $l['thumb'] ) ) $xtpl->parse( 'main.group_content.loop.img' );
					$xtpl->parse( 'main.group_content.loop' );
				}
				$xtpl->parse( 'main.group_content' );
			}
		}
		
		$shop = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module . $global_config['rewrite_exturl'];
		
		$xtpl->assign( 'SHOP', $shop );

		if (!defined('MODAL_LOADED')) {
        $xtpl->parse('main.modal_loaded');
        define('MODAL_LOADED', true);
		}
	
		$xtpl->parse( 'main' );
		return $xtpl->text( 'main' );
	}
}

	
if( defined( 'NV_SYSTEM' ) )
{
		$content = nv_block_news_groups_multi_tabs( $block_config );
}
