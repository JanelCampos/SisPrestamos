<?php

namespace App;

class AuthController
{
    public static function showLogin(): void
    {
        if (Auth::check()) {
            redirect_to('dashboard');
        }

        render('login', [
            'title' => 'Iniciar sesion',
        ]);
    }

    public static function login(): void
    {
        verify_csrf();

        $login = input('login');
        $password = input('password');
        with_old_input(['login' => $login]);

        $service = new AuthService();
        if (!$service->attempt($login, $password)) {
            flash('danger', 'Las credenciales no son validas.');
            redirect_to('login');
        }

        clear_old_input();
        flash('success', 'Bienvenido al sistema.');
        redirect_to('dashboard');
    }

    public static function logout(): void
    {
        verify_csrf();
        Auth::logout();
        flash('success', 'Sesion finalizada correctamente.');
        redirect_to('login');
    }
}

class DashboardController
{
    public static function index(): void
    {
        require_auth();
        $service = new DashboardService();

        render('dashboard', [
            'title' => 'Dashboard',
            'data' => $service->overview(),
        ]);
    }
}

class ClientController
{
    public static function index(): void
    {
        require_auth();
        $service = new ClientService();

        render('clients', [
            'title' => 'Clientes',
            'clients' => $service->all([
                'q' => input('q'),
            ]),
        ]);
    }

    public static function create(): void
    {
        require_auth(['administrador total', 'digitador']);

        render('client_form', [
            'title' => 'Nuevo cliente',
            'client' => null,
            'action' => app_url('clientes/crear'),
        ]);
    }

    public static function store(): void
    {
        require_auth(['administrador total', 'digitador']);
        verify_csrf();

        $payload = self::clientPayload();
        with_old_input($payload);

        try {
            (new ClientService())->save($payload);
            clear_old_input();
            flash('success', 'Cliente registrado correctamente.');
            redirect_to('clientes');
        } catch (\Throwable $throwable) {
            flash('danger', $throwable->getMessage());
            redirect_back('clientes/crear');
        }
    }

    public static function edit(int $id): void
    {
        require_auth(['administrador total', 'digitador']);
        $client = (new ClientService())->find($id);

        if (!$client) {
            abort(404, 'Cliente no encontrado.');
        }

        render('client_form', [
            'title' => 'Editar cliente',
            'client' => $client,
            'action' => app_url('clientes/editar/' . $id),
        ]);
    }

    public static function update(int $id): void
    {
        require_auth(['administrador total', 'digitador']);
        verify_csrf();

        $payload = self::clientPayload();
        with_old_input($payload);

        try {
            (new ClientService())->save($payload, $id);
            clear_old_input();
            flash('success', 'Cliente actualizado correctamente.');
            redirect_to('clientes');
        } catch (\Throwable $throwable) {
            flash('danger', $throwable->getMessage());
            redirect_back('clientes/editar/' . $id);
        }
    }

    public static function show(int $id): void
    {
        require_auth();
        $client = (new ClientService())->find($id);

        if (!$client) {
            abort(404, 'Cliente no encontrado.');
        }

        render('client_show', [
            'title' => 'Expediente del cliente',
            'client' => $client,
        ]);
    }

    public static function destroy(int $id): void
    {
        require_auth(['administrador total']);
        verify_csrf();

        (new ClientService())->deactivate($id);
        flash('success', 'Cliente eliminado de forma segura.');
        redirect_to('clientes');
    }

    public static function searchApi(): void
    {
        require_auth();
        $term = input('q', '');
        $rows = (new ClientService())->search($term);
        json_response([
            'ok' => true,
            'data' => $rows,
        ]);
    }

    private static function clientPayload(): array
    {
        return [
            'foto' => self::storeUpload('foto', 'clientes', input('foto_actual')),
            'nombres' => input('nombres'),
            'dni' => input('dni'),
            'telefono' => input('telefono'),
            'email' => input('email'),
            'direccion' => input('direccion'),
            'nacionalidad' => input('nacionalidad'),
            'tipo_vivienda' => input('tipo_vivienda'),
            'situacion_laboral' => input('situacion_laboral'),
            'estado_civil' => input('estado_civil'),
            'direccion_trabajo' => input('direccion_trabajo'),
            'garante_foto' => self::storeUpload('garante_foto', 'garantes', input('garante_foto_actual')),
            'garante_nombre_completo' => input('garante_nombre_completo'),
            'garante_dni' => input('garante_dni'),
            'garante_telefono' => input('garante_telefono'),
            'garante_direccion' => input('garante_direccion'),
        ];
    }

    private static function storeUpload(string $field, string $folder, ?string $current = null): ?string
    {
        if (empty($_FILES[$field]['name'])) {
            return $current;
        }

        $extension = pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION) ?: 'jpg';
        $filename = $folder . '/' . uniqid('', true) . '.' . strtolower($extension);
        $target = __DIR__ . '/../storage/uploads/' . $filename;

        if (!move_uploaded_file($_FILES[$field]['tmp_name'], $target)) {
            throw new \RuntimeException('No se pudo almacenar la imagen subida.');
        }

        return $filename;
    }
}

class LoanController
{
    public static function index(): void
    {
        require_auth();
        $service = new LoanService();
        $clientService = new ClientService();

        render('loans', [
            'title' => 'Prestamos',
            'loans' => $service->all([
                'estado' => input('estado'),
                'cliente_id' => input('cliente_id'),
                'fecha_desde' => input('fecha_desde'),
                'fecha_hasta' => input('fecha_hasta'),
                'vencimiento' => input('vencimiento'),
            ]),
            'clients' => $clientService->all(),
        ]);
    }

    public static function create(): void
    {
        require_auth(['administrador total', 'digitador']);
        $clientService = new ClientService();
        $loanService = new LoanService();

        render('loan_form', [
            'title' => 'Nuevo prestamo',
            'clients' => $clientService->all(),
            'products' => $loanService->products(),
            'action' => app_url('prestamos/crear'),
        ]);
    }

    public static function store(): void
    {
        require_auth(['administrador total', 'digitador']);
        verify_csrf();

        $payload = self::loanPayload();
        with_old_input($payload);

        try {
            $loanId = (new LoanService())->create($payload, (int) Auth::id());
            clear_old_input();
            flash('success', 'Prestamo registrado correctamente.');
            redirect_to('prestamos/ver/' . $loanId);
        } catch (\Throwable $throwable) {
            flash('danger', $throwable->getMessage());
            redirect_back('prestamos/crear');
        }
    }

    public static function show(int $id): void 
    { 
        require_auth(); 

        $loan = (new LoanService())->find($id); 
    
        if (!$loan) { 
            abort(404, 'Prestamo no encontrado.'); 
        }

        $paymentRequest = (new LoanRepository())->latestPaymentRequest(
            $id,
            (int) Auth::id()
        );
    
        render('loan_show', [ 
            'title' => 'Detalle del prestamo', 
            'loan' => $loan,
            'paymentRequest' => $paymentRequest,
        ]); 
    }

    public static function recordPayment(int $id): void
    {
        require_auth(['administrador total', 'cobrador']);
        verify_csrf();

        try {
            $receipt = (new LoanService())->recordPayment($id, [
                'monto_recibido' => input('monto_recibido'),
                'metodo_pago' => input('metodo_pago'),
                'fecha_pago' => input('fecha_pago'),
                'observacion' => input('observacion'),
            ], (int) Auth::id());
            flash('success', 'Pago registrado correctamente. Recibo: ' . $receipt);
        } catch (\Throwable $throwable) {
            flash('danger', $throwable->getMessage());
        }

        redirect_to('prestamos/ver/' . $id);
    }

    public static function requestPaymentAuthorization(int $id): void
    {
        require_auth(['cobrador']);
        verify_csrf();

        try {
            $loanService = new LoanService();

            $loanService->requestPaymentAuthorization($id, [
                'monto_recibido' => input('monto_recibido'),
                'metodo_pago' => input('metodo_pago'),
                'fecha_pago' => input('fecha_pago'),
                'observacion' => input('observacion'),
            ], (int) Auth::id());

            flash('success', 'Solicitud de autorización enviada correctamente.');
        } catch (\Throwable $throwable) {
            flash('danger', $throwable->getMessage());
        }

        redirect_to('prestamos/ver/' . $id);
    }

    public static function simulateApi(): void
    {
        require_auth();

        try {
            $simulation = (new LoanService())->simulate(self::loanPayload());
            json_response([
                'ok' => true,
                'data' => $simulation,
            ]);
        } catch (\Throwable $throwable) {
            json_response([
                'ok' => false,
                'message' => $throwable->getMessage(),
            ], 422);
        }
    }

    private static function loanPayload(): array
    {
        return [
            'cliente_id' => input('cliente_id'),
            'producto_id' => input('producto_id'),
            'monto_principal' => input('monto_principal'),
            'tasa_interes_tipo' => input('tasa_interes_tipo'),
            'tasa_interes_valor' => input('tasa_interes_valor'),
            'tasa_mora_diaria' => input('tasa_mora_diaria'),
            'fecha_otorgamiento' => input('fecha_otorgamiento'),
            'fecha_primer_pago' => input('fecha_primer_pago'),
            'plazo_cuotas' => input('plazo_cuotas'),
            'frecuencia_pago' => input('frecuencia_pago'),
            'observaciones' => input('observaciones'),
        ];
    }
}

class ReportController
{
    public static function index(): void
    {
        require_auth();
        $loanService = new LoanService();

        render('reports', [
            'title' => 'Reportes',
            'portfolio' => $loanService->all([
                'estado' => input('estado'),
                'fecha_desde' => input('fecha_desde'),
                'fecha_hasta' => input('fecha_hasta'),
            ]),
            'collections' => $loanService->exportReport('cobros', [
                'fecha_desde' => input('fecha_desde'),
                'fecha_hasta' => input('fecha_hasta'),
            ])['rows'],
        ]);
    }

    public static function export(string $type, string $format): void
    {
        require_auth();
        (new ReportService())->export($format, $type, [
            'estado' => input('estado'),
            'fecha_desde' => input('fecha_desde'),
            'fecha_hasta' => input('fecha_hasta'),
        ]);
    }
}

class AdminController
{
    public static function users(): void
    {
        require_auth(['administrador total']);
        $userRepository = new UserRepository();
        $notificationService = new NotificationService();

        render('users', [
            'title' => 'Usuarios y roles',
            'users' => $userRepository->all(),
            'roles' => $userRepository->roles(),
            'pendingNotifications' => $notificationService->pending(),
        ]);
    }

    public static function paymentRequests(): void
    {
        require_auth(['administrador total']);

        $loanRepository = new LoanRepository();

        render('payment_requests', [
            'title' => 'Solicitudes de cobro',
            'requests' => $loanRepository->pendingPaymentRequests(),
        ]);
    }

    public static function approvePaymentRequest(int $id): void
    {
        require_auth(['administrador total']);
        verify_csrf();

        try {
            $loanRepository = new LoanRepository();

            $loanRepository->approvePaymentRequest(
                $id,
                (int) Auth::id()
            );

            flash('success', 'Solicitud de cobro aprobada correctamente.');
        } catch (\Throwable $throwable) {
            flash('danger', $throwable->getMessage());
        }

        redirect_to('solicitudes-cobro');
    }

    public static function rejectPaymentRequest(int $id): void
    {
        require_auth(['administrador total']);
        verify_csrf();

        try {
            $loanRepository = new LoanRepository();

            $loanRepository->rejectPaymentRequest(
                $id,
                (int) Auth::id()
            );

            flash('success', 'Solicitud de cobro rechazada correctamente.');
        } catch (\Throwable $throwable) {
            flash('danger', $throwable->getMessage());
        }

        redirect_to('solicitudes-cobro');
    }

    public static function settings(): void
    {
        require_auth(['administrador total']);

        render('settings', [
            'title' => 'Configuracion',
            'config' => config(),
        ]);
    }
}
