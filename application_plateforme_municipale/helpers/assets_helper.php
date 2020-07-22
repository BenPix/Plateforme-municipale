<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('css_url'))
{
	function css_url($nom)
	{
		return base_url() . 'assets/' . $nom . '.css';
	}
}

if ( ! function_exists('scss_url'))
{
	function scss_url($nom)
	{
		return base_url() . 'assets/' . $nom . '.scss';
	}
}

if ( ! function_exists('js_url'))
{
	function js_url($nom)
	{
		return base_url() . 'assets/' . $nom . '.js';
	}
}

if ( ! function_exists('img_url'))
{
	function img_url($nom)
	{
		return base_url() . 'assets/images/' . $nom;
	}
}

if ( ! function_exists('img'))
{
	function img($nom, $alt = '', $style = '', $extras = '')
	{
		return '<img src="' . img_url($nom) . '" alt="' . $alt . '" style="'.$style.'" '.$extras.'/>';
	}
}

if ( ! function_exists('signature'))
{
	function signature($id, $alt = '', $style = '')
	{
		return '<img src="' . base_url() . 'signatures/signature' . $id . '.png' . '" alt="' . $alt . '" style="'.$style.'" />';
	}
}

if ( ! function_exists('mail_url'))
{
	function mail_url()
	{
		return base_url().'index.php/mail/sendmail';
	}
}

if ( ! function_exists('my_captcha_url'))
{
	function my_captcha_url()
	{
		return base_url() . 'captcha';
	}
}