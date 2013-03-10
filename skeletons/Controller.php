
namespace <?=$this->namespace;?>\controller;

use neptune\controller\Controller;
use neptune\view\View;

class <?=$this->controller_name;?>Controller extends Controller {

	public function index() {
		return View::load('index');
	}

}
