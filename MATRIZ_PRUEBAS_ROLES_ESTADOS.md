# Matriz de pruebas - Roles, estados y pedidos

## Objetivo

Esta matriz sirve para validar el sistema antes y despues de cualquier cambio. El foco es probar lo que Cristian remarco: no alcanza con mirar permisos en base o codigo, hay que entrar con usuarios reales o representativos y verificar que cada rol pueda hacer solo lo que corresponde.

## Usuarios de prueba necesarios

Preparar o identificar usuarios para estos perfiles:

- Usuario General.
- Gerente de Sector / Autorizador.
- Compras Corporativas.
- Asignador de Gestionador.
- Gestionador.
- Asistente de Gestion.
- Analista de Deposito / Entrega.
- Analista de Pagos.
- Auditoria.
- Auditoria Empresa.
- Auditoria Seguros.
- Administrador.

Un usuario puede tener mas de un rol si asi funciona en produccion. Eso debe quedar anotado.

## Datos minimos necesarios

Antes de probar, confirmar que existan:

- Empresas activas.
- Sucursales activas.
- Sectores activos que permitan pedidos.
- Usuarios activos con sucursal y sector.
- Articulos activos.
- Familias/rubros, algunos con stock y otros sin stock.
- Depositos.
- Proveedores.
- Formas de pago.
- Estados de pedido configurados en `ordersstates`.
- Permisos cargados en `permissions` y `permissionsbyrole`.

## Pruebas base

| Caso | Usuario | Accion | Resultado esperado |
| --- | --- | --- | --- |
| Login valido | Todos | Entrar al sistema | Ingresa y ve menu segun permisos |
| Login invalido | Cualquiera | Clave incorrecta | Muestra error |
| Menu pedidos | Usuario con `Orders.See` | Entrar al panel | Ve opcion Pedidos |
| Menu sin permisos | Usuario sin `Orders.See` | Entrar al panel | No ve Pedidos |
| Perfil | Usuario con `MyProfile.See` | Abrir mi perfil | Puede ver perfil |

## Flujo 1 - Alta de pedido

| Caso | Usuario | Estado inicial | Accion | Resultado esperado |
| --- | --- | --- | --- | --- |
| Crear pedido | Usuario General | Nuevo | Cargar cabecera e items | Pedido queda en `TOAUT` |
| Crear con adjuntos | Usuario General | Nuevo | Subir archivo y guardar | Adjunto queda asociado al pedido |
| Crear sin asunto | Usuario General | Nuevo | Guardar sin asunto | Valida campo obligatorio |
| Crear sin articulos | Usuario General | Nuevo | Guardar sin items | Confirmar regla esperada con negocio |
| Seleccionar autorizador | Usuario General | `TOAUT` | Elegir usuario autorizador | Solo muestra autorizadores validos |

## Flujo 2 - Visualizacion y acceso

| Caso | Usuario | Pedido | Accion | Resultado esperado |
| --- | --- | --- | --- | --- |
| Ver pedido propio | Creador | Propio | Abrir listado | Lo ve |
| Ver pedido asignado | Gestionador | Asignado a el | Abrir listado | Lo ve |
| Ver pedido como autorizador | Autorizador | Asignado a el | Abrir listado | Lo ve |
| Ver pedido de empresa | Rol con `CompanyAccess` | Misma empresa | Abrir listado | Lo ve |
| Ver todos | Rol con `FullAccess` | Cualquiera | Abrir listado | Lo ve |
| Auditoria seguros | Rol con `FullAccessInsurance` | Pedido seguro | Abrir listado | Lo ve segun regla |
| Usuario no relacionado | Usuario comun | Pedido ajeno | Abrir listado | No deberia verlo |

## Flujo 3 - Validacion / autorizacion

| Caso | Usuario | Estado inicial | Accion | Resultado esperado |
| --- | --- | --- | --- | --- |
| Autorizar pedido | Gerente/Autorizador | `TOAUT` | Cambiar a `AUT` | Pedido queda autorizado |
| Rechazar pedido | Gerente/Autorizador | `TOAUT` | Cambiar a `NOTAUT` con observacion | Pedido queda rechazado |
| Rechazar sin observacion | Gerente/Autorizador | `TOAUT` | Cambiar a `NOTAUT` sin observacion | Debe exigir observacion |
| Usuario sin permiso autoriza | Usuario General | `TOAUT` | Intentar cambiar estado | No debe permitir |
| Autorizador por sector | Rol `AuthorizateSector` | `TOAUT` | Validar pedido de sector correspondiente | Debe permitir |
| Autorizador por empresa | Rol `AuthorizateCompany` | `TOAUT` | Validar pedido de empresa correspondiente | Debe permitir |

## Flujo 4 - Asignacion de gestion

| Caso | Usuario | Estado inicial | Accion | Resultado esperado |
| --- | --- | --- | --- | --- |
| Asignar gestionador | Rol `AssignManager` | `AUT` | Seleccionar gestionador | Pedido pasa/queda gestionable |
| Tomar pedido | Gestionador permitido | `AUT` sin manager | Abrir y guardar gestion | Queda asignado correctamente |
| Cambiar gestionador | Rol `AssignManager` | En gestion | Cambiar manager | Se actualiza y registra historial |
| Asignar asistente | Manager o asignador | En gestion | Seleccionar submanager | Se guarda asistente |
| Asistente igual a manager | Manager/asignador | En gestion | Seleccionar mismo usuario | Debe rechazar |
| Asistente sin manager | Manager/asignador | En gestion | Elegir asistente sin manager | Debe rechazar |

## Flujo 5 - Gestion del pedido

| Caso | Usuario | Estado inicial | Accion | Resultado esperado |
| --- | --- | --- | --- | --- |
| En gestion | Gestionador | `PROG` | Editar gestion | Permite acciones de manager |
| Pedir detalles | Gestionador | `PROG` | Cambiar a `DETUSR` | Usuario queda notificado si aplica |
| Presupuestar | Gestionador | `PROG` | Cambiar a `BUDGET` | Permite cargar presupuestos |
| Pendiente compra | Gestionador | `BUDGET` | Cambiar a `AUTBUY` | Estado actualizado |
| Pendiente pago | Gestionador | `AUTBUY` | Cambiar a `TOPAY` | Estado actualizado |
| Pendiente entrega | Gestionador | `TOPAY` | Cambiar a `READY` | Pedido listo para entrega |
| Anular | Usuario con permiso | Estado permitido | Cambiar a `CAN` | No debe permitir si hay remitos |
| Suspender | Usuario con permiso | Estado permitido | Cambiar a `SUS` | No debe permitir si hay remitos |

## Flujo 6 - Presupuestos

| Caso | Usuario | Estado pedido | Accion | Resultado esperado |
| --- | --- | --- | --- | --- |
| Ver presupuestos | Rol `Budgets.See` | Pedido existente | Abrir presupuestos | Lista presupuestos |
| Cargar presupuesto | Rol `Budgets.Insert` | Estado editable | Cargar proveedor/items | Presupuesto guardado |
| Adjuntar presupuesto | Rol `Budgets.Insert` | Estado editable | Subir adjunto | Archivo queda asociado |
| Seleccionar item | Rol `Budgets.Insert` | Estado editable | Seleccionar item presupuestado | Marca seleccionado |
| Cargar en estado no editable | Rol `Budgets.Insert` | `FIN`/`CAN`/`NOTAUT` | Intentar cargar | No debe permitir |

## Flujo 7 - Remitos y entrega

| Caso | Usuario | Estado pedido | Accion | Resultado esperado |
| --- | --- | --- | --- | --- |
| Generar remito | Rol `GenerateDN` | `READY` | Generar remito | Remito creado |
| Generar remito parcial | Rol `GenerateDN` | `READY` | Entregar parte | Cantidades actualizadas |
| Recibir remito | Rol `GenerateDN` | `READY`/`INDIST`/`PENSUP` | Cargar recepcion | Fecha/persona guardadas |
| Finalizar con pendientes | Gestionador | `READY`/`INDIST` | Cambiar a `FIN` | Debe impedir si falta entregar |
| Finalizar con recepcion incompleta | Gestionador | Pedido entregado | Cambiar a `FIN` | Debe impedir si remito incompleto |
| Finalizar correcto | Gestionador | Todo entregado | Cambiar a `FIN` | Pedido finalizado |

## Flujo 8 - Stock

| Caso | Usuario | Accion | Resultado esperado |
| --- | --- | --- | --- |
| Ver movimientos | Rol `StockMovements.See` | Abrir listado | Ve movimientos |
| Ingreso manual | Rol `StockMovements.Insert` | Crear movimiento entrada | Aumenta disponibilidad |
| Egreso manual | Rol `StockMovements.Insert` | Crear movimiento salida | Descuenta si hay stock |
| Egreso sin stock | Rol `StockMovements.Insert` | Superar disponible | Debe mostrar error |
| Remito descuenta stock | Rol entrega | Generar entrega | Movimiento/stock reflejado |
| Anular pedido reasigna | Rol permitido | Anular pedido con stock asignado | Stock se reasigna |

## Flujo 9 - Reportes y exportaciones

| Caso | Usuario | Accion | Resultado esperado |
| --- | --- | --- | --- |
| Exportar pedidos | Rol `Orders.Export` | Export simple | Descarga archivo |
| Export detallado | Rol `Orders.FullExport` | Export completo | Descarga detalle |
| Ver importes | Rol `Orders.SeeImports` | Abrir pedido/listado | Ve valores |
| Sin ver importes | Usuario sin permiso | Abrir pedido/listado | No ve valores |
| Reporte stock | Rol `Reports.Stock` | Abrir reporte | Ve reporte |
| Reporte general | Rol `Reports.General` | Abrir reporte | Ve reporte |

## Flujo 10 - Adjuntos

| Caso | Usuario | Accion | Resultado esperado |
| --- | --- | --- | --- |
| Subir adjunto pedido | Usuario con permiso | Subir archivo | Se guarda temporalmente y luego en pedido |
| Descargar adjunto temporal | Mismo usuario | Descargar antes de guardar | Descarga desde temporal |
| Guardar pedido con adjunto | Usuario con permiso | Guardar pedido | Mueve de temporal a carpeta del pedido |
| Descargar adjunto guardado | Usuario con permiso | Descargar desde pedido | Descarga archivo correcto |
| Eliminar adjunto propio | Usuario que lo subio | Eliminar | Se marca eliminado |
| Eliminar adjunto ajeno | Otro usuario | Intentar eliminar | No deberia permitir |

## Validaciones tecnicas despues de cada cambio

- Revisar que no haya errores PHP visibles.
- Revisar logs de CodeIgniter.
- Probar login/logout.
- Probar menu.
- Probar listado de pedidos.
- Probar alta y edicion de pedido.
- Probar permisos con al menos dos roles distintos.
- Probar exportacion si se tocaron consultas.
- Probar stock si se tocaron articulos, remitos, pedidos o facturas.
- Probar adjuntos si se tocaron vistas, Dropzone o rutas de archivos.

## Resultado esperado del proceso

Cada cambio deberia salir con:

- Que funcionalidad se cambio.
- Que archivos se tocaron.
- Que roles se probaron.
- Que estados se probaron.
- Que casos quedaron pendientes.
- Capturas o notas breves si algo depende de decision de negocio.
