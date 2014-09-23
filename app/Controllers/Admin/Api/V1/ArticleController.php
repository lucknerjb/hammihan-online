<?php namespace Controllers\Admin\Api\V1;

use Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Translation\Translator;
use Laracasts\Commander\CommanderTrait;
use Laracasts\Validation\FormValidationException;
use Pardisan\Repositories\Exceptions\RepositoryException;
use Illuminate\Auth\AuthManager;

class ArticleController extends BaseController {

    use CommanderTrait;

    /**
     * @var AuthManager
     */
    protected $auth;
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var Translator
     */
    protected $lang;

    /**
     * @param Request $request
     * @param Translator $lang
     */
    public function __construct(
        Request $request,
        Translator $lang,
        AuthManager $auth

    ){
        $this->request = $request;
        $this->lang = $lang;
        $this->auth = $auth;
    }

    /**
     * Store an article in db
     */
    public function store()
    {
        sleep(2);
        $input = $this->request->only(
            'first_title',
            'second_title',
            'important_title',
            'summary',
            'body',
            'publish_date',
            'status_id',
            'author'
        );

        $input['user_id'] = $this->auth->user()->id;


        try {

            $created = $this->execute(
                'Pardisan\Commands\Article\NewCommand',
                $input
            );

            return $this->responseJson($created, 200);

        }catch (RepositoryException $e){

            return $this->responseJson(['errors' => [[$this->lang->get('messages.repository_error')]]], 422);

        }catch (FormValidationException $e){

            return $this->responseJson(['errors' => $e->getErrors()], 422);

        }
    }
} 