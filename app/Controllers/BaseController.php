<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    /** @var list<string> */
    protected $helpers = ['url', 'form', 'attendance', 'license'];

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
    }

    /** İstek AJAX (modal) ile mi geldi? */
    protected function wantsJson(): bool
    {
        return $this->request->isAJAX();
    }

    /**
     * Modal AJAX başarı yanıtı.
     * Flash mesajı sonraki sayfaya taşınır; JS verilen adrese yönlendirir,
     * böylece toast yeniden yüklenen listede görünür.
     */
    protected function jsonOk(string $redirect, string $message = '')
    {
        if ($message !== '') {
            session()->setFlashdata('message', $message);
        }

        return $this->response->setJSON([
            'ok'       => true,
            'redirect' => $redirect,
        ]);
    }

    /**
     * Modal AJAX hata yanıtı.
     * Modal açık kalır, hata bandına mesaj yazılır; taze CSRF token'ı
     * bir sonraki gönderim için geri gönderilir.
     */
    protected function jsonError(string $message)
    {
        return $this->response->setJSON([
            'ok'    => false,
            'error' => $message,
            'csrf'  => ['name' => csrf_token(), 'hash' => csrf_hash()],
        ]);
    }
}
