<?php

/**
 * This is a simple BaseController that all Controllers should extend.
 *
 * It provides functions for rendering the views into the layout. If a layout
 * is not used, the regular View::make() function should be used in the
 * controller.
 */

abstract class BaseController extends Controller {

	/**
     * Render a view into the layout variable or return it.
     *
     * @param string $name View name (file)
     * @param array $variables Variables to be passed onto view
     * @param string|null $title Page title
     * @return \Illuminate\View\View
     */
	public function render($name, array $variables = array(), $title = null)
	{
		$view = View::make($name, $variables);

		if(!is_null($this->layout))
		{
			if(!is_null($title)) {
				if(is_array($title)):
					$this->layout->title = $title[0];
					$this->layout->subtitle = $title[1];
				else:
					$this->layout->title = $title;
				endif;
			}

			$this->layout->content = $view;
		}
		
		return $view;
	}

	/**
     * Auto render based on namespace, controller and method names.
     *
     * @return \Illuminate\View\View
     * @throws Exception
     * @see BaseController::render()
     */
	public function autoRender()
	{
		list(, $caller) = debug_backtrace(false);
		$namespace = trim($caller['class'],'\\');
		$method = $caller['function'];
		if(ends_with($namespace,'Controller'))
		{
			$namespaces = explode('\\',strtolower(substr($namespace,0,-10)));
			$namespaces[] = $method;

			$args = array_merge(array(implode('.', $namespaces)), func_get_args());

			return call_user_func_array(array($this,'render'), $args);
		}
		else
		{
			throw new Exception("Cannot auto-render view. Controller name does not end with 'Controller'.");
		}
	}

	/**
     * Set up layout
     *
     * @return void|\Illuminate\View\View
     */
	protected function setupLayout()
	{
		if ( ! is_null($this->layout) && !$this->layout instanceof Illuminate\View\View)
		{
			$this->layout = View::make($this->layout);
			$this->layout->stylesheets = array();
			$this->layout->javascripts = array();
			return $this->layout;
		}
	}

	/**
     * Allow the layout to be changed manually from the Controller
     *
     * @param string $view View name
     * @return void
     */
	public function customLayout($view)
	{
		$this->layout = 'layouts.' . $view;
		$this->setupLayout();
	}

	/**
     * Add javascript file to the layout
     *
     * @param string $javascript Path to file
     * @return void
     */
	public function javascript($javascript)
	{
		$javascripts = $this->layout->javascripts;
		$javascripts[] = $javascript;
		$this->layout->javascripts = $javascripts;
	}

	/**
     * Add stylesheet file to the layout
     *
     * @param string $stylesheet Path to file
     * @return void
     */
	public function stylesheet($stylesheet)
	{
		$stylesheets = $this->layout->stylesheets;
		$stylesheets[] = $stylesheet;
		$this->layout->stylesheets = $stylesheets;
	}

}