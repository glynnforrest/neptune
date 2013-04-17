
namespace <?=$this->namespace;?>\Controller;

use Neptune\Controller\Controller;
use Neptune\View\View;

<?=$this->class_info;?>
class <?=$this->controller_name;?> extends Controller {

    public function index() {
        return View::load('index');
    }

}
