<?php namespace Controllers\Admin; 

use Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Illuminate\Translation\Translator;
use Laracasts\Validation\FormValidationException;
use Pardisan\Repositories\Exceptions\NotFoundException;
use Pardisan\Repositories\Exceptions\RepositoryException;
use Pardisan\Repositories\RoleRepositoryInterface;
use Pardisan\Repositories\UserRepositoryInterface;

class UserController extends BaseController
{
    /**
     * @var Translator
     */
    protected $lang;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var UserRepositoryInterface
     */
    protected $userRepo;

    /**
     * @var RoleRepositoryInterface
     */
    protected $roleRepo;

    /**
     * @param Request $request
     * @param Translator $lang
     * @param UserRepositoryInterface $userRepo
     * @param RoleRepositoryInterface $roleRepo
     */
    public function __construct(
        Request $request,
        Translator $lang,
        UserRepositoryInterface $userRepo,
        RoleRepositoryInterface $roleRepo
    ){
        $this->request = $request;
        $this->lang = $lang;
        $this->userRepo = $userRepo;
        $this->roleRepo = $roleRepo;
    }

    /**
     * List users
     *
     * @return View
     */
    public function index()
    {
        $users = $this->execute('Pardisan\Commands\User\IndexCommand');

        return $this->view(
            'salgado.pages.user.index',
            compact('users')
        );
    }

    /**
     * User Creation form
     *
     * @return View
     */
    public function create()
    {
        return $this->view('salgado.pages.user.create_edit');
    }

    /**
     * User update form
     *
     * @param $id
     * @return View
     */
    public function edit($id)
    {
        try {
            // @TODO Move this part to a sparate command
            $user = $this->userRepo->getUserWithRoles($id);
            $roles = $this->roleRepo->getAll();
            $userRoles = $user->roles->lists('id');
            return $this->view(
                'salgado.pages.user.create_edit',
                compact('user', 'roles', 'userRoles')
            );

        }catch (NotFoundException $e){
            App::abort(404);
        }
    }

    /**
     * User deletion
     *
     * @param $id
     * @return Redirect
     */
    public function destroy($id)
    {
        return $this->generalDestroy($id, 'Pardisan\Commands\User\BulkDeleteCommand');
    }

    /**
     * Storing a user in db
     *
     * @return Redirect
     */
    public function store()
    {
        try {

            $stored = $this->execute('Pardisan\Commands\User\StoreCommand');

            return $this->redirectRoute('admin.users.index')->with(
                'success_message',
                $this->lang->get('messages.users.store_success', ['email' => $stored->email])
            );

        }catch (FormValidationException $e){

            return $this->redirectBack()->withInput()->withErrors($e->getErrors());

        }catch(RepositoryException $e){

            return $this->redirectBack()->with(
                'error_message',
                $this->lang->get('messages.repository_error')
            );

        }
    }

    /**
     * Updating a user in db
     *
     * @param $id
     * @return Redirect
     */
    public function update($id)
    {
        $this->request->merge(['id' => $id]);
        try {

            $updated = $this->execute('Pardisan\Commands\User\UpdateCommand');

            return $this->redirectBack()->with(
                'success_message',
                $this->lang->get('messages.users.update_success', ['id' => $updated->id])
            );

        }catch (NotFoundException $e){

            App::abort(404);

        }catch(RepositoryException $e){

        }
    }
} 
