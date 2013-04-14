
namespace <?=$this->namespace;?>\Controller;

use Neptune\Controller\Controller;
use Neptune\View\View;

class <?=$this->controller_name;?> extends Controller {

    public function index() {
        return View::load('index');
    }

}
