<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Jarvix Plus
 *
 * @package		Jarvix Plus
 * @author		Junior Furini
 * @copyright		Copyright (c) 2012 - 2012, Jarvix Plus.
 * @license		http://jarvixplus.com/license.html
 * @link		http://jarvixplus.com
 * @since		Version 0.0.1
 * @filesource		/application/libraries/JX_Pagination.php
 * 
 * $Id: JX_Pagination.php,v 1.6 2013-03-16 19:02:02 junior Exp $
 * 
 */

class JX_Pagination
{
	protected $CI;

	var $use_pagination		= TRUE;
	var $total_rows			= 0; // Total number of items (database results)
	var $start_line			= 0;
	var $last_line			= 0;
	var $lines_per_page		= 30; // Max number of items you want shown per page
	var $cur_page			= 1; // The current page being viewed

	/**
	 * Constructor
	 *
	 * @access	public
	 * @param	array	initialization parameters
	 */
	public function __construct()
	{
		$this->CI =& get_instance();

		log_message('debug', "JX_Pagination Class Initialized");
	}

	// --------------------------------------------------------------------

	/**
	 * Initialize Preferences
	 *
	 * @access	public
	 * @param	array	initialization parameters
	 * @return	void
	 */
	function initialize($params = array())
	{
		if (count($params) > 0)
		{
			foreach ($params as $key => $val)
			{
				if (isset($this->$key))
				{
					$this->$key	=	$val;
				}
			}
		}
		
		if ( isset( $this->CI->singlepack->user_cfg->lines_per_page ) )
		{
			$this->lines_per_page			= $this->CI->singlepack->user_cfg->lines_per_page;
		}

		// Calcula linha inicial.
		if ( $this->total_rows == 0
		OR   $this->lines_per_page == 0
		   )
		{
			$this->start_line	=	1;
			$this->last_line	=	$this->total_rows;
		}
		else
		{
			if ( $this->cur_page == -1 ) // Solicitada a última página.
			{
				$this->cur_page	=	ceil( $this->total_rows / $this->lines_per_page );
			}

			$this->start_line	=	( ( $this->cur_page - 1 ) * $this->lines_per_page ) + 1;
			$this->last_line	=	( $this->start_line + $this->lines_per_page ) -1;
		}
		if ( $this->last_line > $this->total_rows )
		{
			$this->last_line	=	$this->total_rows;
		}
	}

	// --------------------------------------------------------------------

	function create_links()
	{
		$output			=	NULL;
		if ( $this->use_pagination === TRUE )
		{
			$output		=	'
						<p>'.( $this->start_line ).' - '.( $this->last_line ).' de '.$this->total_rows.'</p>
						';
			if ( $this->cur_page == 1 )
			{
				$output		.=	'
							<div class="button small previous inativo" title="Anterior">
							';
			}
			else
			{
				$output		.=	'
							<div class="button small previous" title="Anterior">
							';
			}
				$output		.=	'
								<div class="button image previous">
								</div>
							</div>
						';
	
			if ( $this->last_line == $this->total_rows )
			{
				$output		.=	'
							<div class="button small next inativo" title="Próxima">
							';
			}
			else
			{
				$output		.=	'
							<div class="button small next" title="Próxima">
							';
			}
				$output		.=	'
								<div class="button image next">
								</div>
							</div>
							';
		}
		return $output;
	}
		
	public function get_lines_per_page()
	{
		if ( $this->use_pagination )
		{
			return $this->lines_per_page;
		}
		else
		{
			return $this->total_rows;
		}
	}

	function get_start_line()
	{
		if ( $this->use_pagination )
		{
			return $this->start_line;
		}
		else
		{
			return 1;
		}
	}

	function get_last_line()
	{
		if ( $this->use_pagination )
		{
			return $this->last_line;
		}
		else
		{
			return $this->total_rows;
		}
	}

	function get_cur_page()
	{
		return $this->cur_page;
	}
	
	function get_total_lines()
	{
		return $this->total_rows;
	}
}

/* End of file JX_Pagination.php */
/* Location: /application/libraries/JX_Pagination.php */