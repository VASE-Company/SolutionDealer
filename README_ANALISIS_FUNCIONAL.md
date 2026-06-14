# Solution Dealer - Compras Corporativas

## Proposito de este documento

Este README resume el entendimiento inicial del sistema para compartir entre Cristian, Darian, Dante y el equipo. La idea no es proponer una reescritura total, sino dejar claro como funciona hoy el sistema, donde estan las reglas importantes y como conviene avanzar con ayuda de IA sin romper flujos existentes.

## Contexto aportado por Cristian

Cristian aclaro varios puntos importantes:

- La base entregada es la que usa para desarrollar. La estructura de tablas/campos sirve, pero los datos pueden estar viejos.
- El sistema esta hecho en CodeIgniter, una tecnologia vieja. Eso no deberia distraernos del objetivo actual: entregar la funcionalidad pedida sobre lo que ya existe.
- Una IA seguramente va a sugerir modernizar, reestructurar o rehacer partes. Eso puede servir como diagnostico, pero no debe ser el primer paso.
- Hay permisos definidos en base de datos por rol, pero tambien hay reglas que dependen de la posicion del usuario dentro del pedido: quien lo crea, quien valida, quien gestiona, quien asiste, quien entrega, etc.
- Para entender bien el sistema no alcanza con leer codigo. Hay que entrar como distintos usuarios y usarlo: usuario solicitante, validador/gerente, asignador, gestionador, asistente de gestion, deposito/entrega, auditoria.
- Cada cambio hecho con IA debe probarse muchas veces, porque puede modificar una parte y dejar otra regla desconectada.

Este analisis confirma esa advertencia: el sistema tiene reglas de negocio repartidas entre codigo PHP, tablas de permisos, tablas de estados y uso real por rol.

## Stack tecnico

- Framework: CodeIgniter 3.1.9.
- Lenguaje: PHP.
- Base de datos: MySQL/MariaDB.
- Frontend: vistas PHP, jQuery, Bootstrap/AdminLTE y JavaScript propio.
- Exportaciones: PhpSpreadsheet, PHPExcel legacy y Dompdf.
- Dump recibido: `sd_corporate_purchases.sql`.
- Aplicacion: `corporatePurchases`.

Archivos clave:

- `corporatePurchases/application/controllers/Orders.php`
- `corporatePurchases/application/models/Orders_model.php`
- `corporatePurchases/application/controllers/Roles.php`
- `corporatePurchases/application/models/Roles_model.php`
- `corporatePurchases/application/controllers/Users.php`
- `corporatePurchases/application/models/Users_model.php`
- `corporatePurchases/application/controllers/Budgets.php`
- `corporatePurchases/application/models/Budgets_model.php`
- `corporatePurchases/application/controllers/StockMovements.php`
- `corporatePurchases/application/models/StockMovements_model.php`
- `corporatePurchases/application/libraries/My_application.php`

## Que hace el sistema

El sistema gestiona compras corporativas. El flujo principal gira alrededor de pedidos internos que pueden ser creados por usuarios, validados por responsables, asignados a gestionadores, presupuestados, comprados, entregados y finalmente cerrados.

Ademas del pedido principal, hay modulos relacionados:

- Usuarios y roles.
- Empresas, sucursales y sectores.
- Articulos, rubros/familias y stock.
- Proveedores.
- Presupuestos.
- Remitos/entregas.
- Facturas.
- Reportes.
- Notificaciones.
- Historial de cambios del pedido.

## Flujo general de un pedido

1. El usuario ingresa al sistema.
2. El menu se arma segun los permisos del rol.
3. El usuario crea un pedido.
4. El pedido nace normalmente en estado `TOAUT`, pendiente de validacion.
5. Un usuario con permiso de autorizacion lo valida o lo rechaza.
6. Si se autoriza, puede pasar a estado `AUT`.
7. Un usuario con permiso de asignacion o gestion toma el pedido y lo lleva a gestion.
8. El pedido puede pasar por estados de analisis, presupuestos, autorizacion de compra, pago, entrega o proveedor.
9. Se pueden generar presupuestos vinculados al pedido y seleccionar items.
10. Cuando corresponde, se generan remitos y se actualizan cantidades entregadas.
11. Si todo fue entregado y los datos de recepcion estan completos, el pedido puede finalizarse.
12. Todo cambio relevante queda registrado en historial y puede generar notificaciones.

## Estados principales del pedido

Los estados estan configurados en la tabla `ordersstates`. No son solo constantes en codigo.

Estados detectados:

- `TOAUT`: Pendiente Validacion.
- `AUT`: Autorizado.
- `PROG`: En Gestion.
- `DETUSR`: Pendiente Detalles Usuario.
- `BUDGET`: Presupuestando.
- `AUTBUY`: Pendiente Autorizacion Compra / Analisis.
- `TOPAY`: Pendiente de Pago.
- `READY`: Pendiente de Entrega.
- `INDIST`: En Reparto.
- `PENSUP`: Pendiente Recepcion Proveedor.
- `FIN`: Finalizado.
- `CAN`: Anulado.
- `SUS`: Suspendido.
- `NOTAUT`: Rechazado.

La tabla `ordersstates` tambien define que proximo estado puede elegir cada tipo de usuario:

- Usuario solicitante.
- Usuario autorizador.
- Gestionador.
- Usuario de entrega/deposito.

Por eso un cambio de estado debe revisarse en codigo y en datos.

## Permisos y roles

Hay dos capas de permisos:

### 1. Permisos por rol

Se configuran en base de datos:

- `permissions`
- `permissionsbyrole`
- `roles`
- `rolesbyuser`

La libreria `My_application.php` usa esos permisos para decidir si un usuario puede ver, editar, exportar, imprimir, administrar, etc.

Ejemplos de permisos importantes:

- `Orders.See`
- `Orders.Insert`
- `Orders.Edit`
- `Orders.Delete`
- `Orders.Export`
- `Orders.Print`
- `Orders.FullAccess`
- `Orders.FullAccessInsurance`
- `Orders.CompanyAccess`
- `Orders.IsAuthorizingUser`
- `Orders.AuthorizateSector`
- `Orders.AuthorizateCompany`
- `Orders.AuthorizedByAny`
- `Orders.IsManager`
- `Orders.AssignManager`
- `Orders.GenerateDN`
- `Orders.SeeImports`
- `Orders.SeeInternalObservations`
- `Orders.SeeAttachments`
- `Orders.FullCancel`
- `Orders.ItemCancel`
- `Budgets.See`
- `Budgets.Insert`
- `Budgets.Delete`
- `StockMovements.See`
- `StockMovements.Insert`
- `StockMovements.Export`

### 2. Permisos por posicion dentro del pedido

Aunque un usuario tenga permisos generales, el sistema tambien evalua su relacion con el pedido:

- Si es el usuario que lo creo.
- Si es el usuario autorizador.
- Si es el gestionador principal.
- Si es asistente de gestion.
- Si pertenece a la misma empresa/sucursal/sector.
- Si tiene acceso a toda la empresa.
- Si tiene acceso total.
- Si el pedido esta en un estado editable para ese rol.

Esta logica esta especialmente en:

- `Orders_model::getAccessFilter()`
- `Orders_model::currentUserCanAutorizateOrder()`
- `Orders.php::edit()`
- `Orders_model::setOrder()`

Este punto es clave: no alcanza con mirar la tabla de permisos.

## Roles principales detectados

Roles activos relevantes:

- Administrador.
- Usuario General.
- Gerente de Sector.
- Compras Corporativas.
- Compras Asigna Gestionador.
- Autorizar Sector General.
- Autorizar Empresa General.
- Validado Por Cualquier Gerente de la Empresa.
- Acceso Pedidos Empresa.
- Exportar Pedidos Detallados.
- Analista de Deposito.
- Acceso a Stock.
- Auditoria.
- Analista de Pagos.
- Auditoria Seguros.
- Auditoria Empresa.

Algunos roles parecen pensados como complementarios. Es decir, un usuario puede tener mas de un rol para sumar permisos especificos.

## Modulos relacionados al pedido

### Pedidos

Modulo central. Maneja:

- Alta y edicion de pedidos.
- Detalle de articulos.
- Estados.
- Autorizador.
- Gestionador y asistente.
- Observaciones de usuario.
- Observaciones internas.
- Adjuntos.
- Imputacion de pagos por empresa/sucursal/sector.
- Impresion/exportacion.
- Historial.
- Remitos.
- Cancelacion de items.

### Presupuestos

Permite cargar presupuestos asociados al pedido o a items del pedido.

Maneja:

- Proveedor.
- Fecha.
- Origen.
- Forma de pago.
- Items presupuestados.
- Seleccion de item presupuestado.
- Adjuntos.
- Resumen por pedido.

### Stock

El stock se cruza con articulos, familias, depositos, facturas, movimientos manuales y remitos.

Puntos importantes:

- Algunas familias de articulos afectan stock y otras no.
- Al autorizar o gestionar un pedido se puede calcular cantidad lista/disponible.
- Los remitos descuentan stock.
- Al cancelar o anular puede haber reasignacion de stock.

### Remitos / entregas

Se generan desde pedidos en estados vinculados a entrega:

- `READY`
- `INDIST`
- `PENSUP`

Para finalizar un pedido se valida que:

- No queden articulos pendientes de entregar.
- Los remitos tengan datos de recepcion completos.

## Notificaciones e historial

El sistema guarda historial en `historybyorder` y tipos en `historytypes`.

Eventos detectados:

- Alta de pedido.
- Cambio de estado.
- Gestionador asignado.
- Asistente de gestion asignado.
- Impresion/PDF de orden de compra.
- Impresion/PDF de remito.
- Cancelacion de articulos.

Las notificaciones dependen del estado y de `usersToNotify` configurado en `ordersstates`.

## Riesgos tecnicos actuales

Estos puntos no significan que haya que reescribir, pero si que hay que trabajar con cuidado:

- No hay una suite de tests propia visible para validar automaticamente los flujos.
- Muchas consultas SQL estan armadas a mano por concatenacion.
- Hay configuraciones sensibles versionadas. No conviene compartir credenciales por el grupo y deberian rotarse si corresponden a entornos reales.
- La base recibida puede tener datos viejos, aunque la estructura sirve.
- Hay codigo legacy en carpetas `BCK`; no deberia tomarse como flujo actual sin confirmarlo.
- Hay una posible inconsistencia en carga de adjuntos: `Dropzone` usa una variable de sesion `companyId`, pero el login actual setea `userCompanyId`; ademas los modelos mueven adjuntos desde `files/tmp/`.
- El entorno local debe replicar version de PHP/extensiones lo mas cercano posible para evitar errores por compatibilidad.

## Forma recomendada de avanzar

1. Levantar entorno local con el dump recibido.
2. Confirmar version de PHP compatible con CodeIgniter 3 y dependencias actuales.
3. Crear o identificar usuarios representativos por rol:
   - Usuario comun.
   - Gerente/autorizador.
   - Compras corporativas.
   - Asignador de gestionador.
   - Gestionador.
   - Asistente de gestion.
   - Deposito/entrega.
   - Auditoria.
4. Entrar al sistema con cada usuario y documentar que ve y que puede hacer.
5. Armar matriz de pruebas por flujo antes de tocar codigo.
6. Hacer cambios chicos y controlados.
7. Probar cada cambio con los roles afectados.
8. Registrar decisiones de negocio que no esten claras en codigo.

## Matriz minima de pruebas manuales

Antes de modificar funcionalidades importantes, probar:

- Login y menu por rol.
- Alta de pedido como usuario general.
- Edicion de pedido propio en `TOAUT`.
- Seleccion de autorizador.
- Validacion de pedido por gerente/autorizador.
- Rechazo de pedido y observacion obligatoria.
- Visualizacion de pedido por usuario no relacionado.
- Acceso por empresa/sucursal/sector.
- Asignacion de gestionador.
- Cambio de gestionador y asistente.
- Transiciones de estados de gestion.
- Carga de presupuestos.
- Seleccion de presupuesto/item.
- Carga y descarga de adjuntos.
- Generacion de orden de compra.
- Generacion de remito.
- Carga de recepcion de remito.
- Finalizacion de pedido.
- Anulacion/suspension.
- Cancelacion parcial de item.
- Exportacion simple y exportacion detallada.
- Reportes.
- Impacto en stock.
- Historial y notificaciones.

## Criterio de trabajo con IA

La IA puede servir para:

- Mapear codigo y tablas.
- Detectar riesgos.
- Explicar flujos.
- Hacer cambios puntuales.
- Ayudar a escribir pruebas o checklist.
- Refactorizar partes chicas si hay verificacion posterior.

La IA no deberia usarse para:

- Reescribir todo el sistema sin entender el uso real.
- Cambiar estados o permisos sin validar con usuarios.
- Modificar consultas compartidas sin probar reportes, exportaciones y pantallas afectadas.
- Asumir reglas de negocio que no esten confirmadas por Cristian/Darian/Dante o por uso real del sistema.

## Pendientes para confirmar con el equipo

- Cuales son las funcionalidades nuevas pedidas exactamente.
- Que usuarios reales representan cada rol.
- Que datos del dump son confiables para pruebas.
- Si los adjuntos estan funcionando actualmente.
- Si la carpeta `BCK` se usa en algun entorno o solo es respaldo historico.
- Cual es el entorno real de PHP/MySQL/Apache.
- Si hay credenciales expuestas que deben rotarse.
- Cuales son los flujos que mas riesgo tienen para produccion.

## Conclusion

El sistema es viejo, pero tiene una logica de negocio concreta y funcionando sobre CodeIgniter. El foco deberia estar en entender y preservar los flujos actuales mientras se agregan o corrigen funcionalidades puntuales.

La advertencia principal de Cristian es correcta: antes de pedirle a la IA que cambie codigo, hay que embarrarse usando el sistema con distintos roles. Despues, cada cambio debe ser chico, verificable y probado contra la matriz de roles y estados.
