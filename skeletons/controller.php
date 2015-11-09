
namespace <?=$this->namespace;?>\Controller;

use Neptune\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;

<?=$this->class_info;?>
class <?=$this->controller_name;?> extends Controller
{
    public function indexAction(Request $request)
    {
        return 'Hello from <?$this->class_info;?>';
    }
}
