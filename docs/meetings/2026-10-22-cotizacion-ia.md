# Helios — Acuerdos para junta

22 de octubre de 2026 · Consolidación vigente

## Helios | Cotización con Ingeniero de IA

Documento para junta · 22 de octubre de 2026

### Propósito

Acordar el flujo que convierte un recibo CFE en una cotización de equipos, mediante un Ingeniero de IA que analiza el caso y utiliza herramientas de cálculo y validación.

### Decisión central

La instalación NO está incluida. Se cotiza por separado. Los precios capturados por la empresa son SIN IVA y SIN INSTALACIÓN. La cotización muestra subtotal, IVA y total.

### Primera entrega

Solo interconexión CFE, dimensionada al 100% del consumo diario calculado según Syscom. Equipos de Syscom inicialmente. Aislado, híbrido, proveedores adicionales e inventario propio son posibilidades posteriores.

### Dos entradas, un análisis

Oficina: asesor registra o selecciona al lead, carga el recibo y obtiene cotización en CRM. Embebido: lead registra sus datos y recibo, se guarda en CRM y el análisis inicia automáticamente.

### Estado real

Esta conversación produjo definición de dominio, RFC y ADR. No implementó el cotizador, el Ingeniero IA real, el catálogo HSP, la entrega de propuestas ni la geocodificación simulada. La lectura de archivos existente continúa siendo mock.

### Cómo leer este documento

Las reglas indicadas como acordadas provienen de respuestas explícitas del usuario. Las recomendaciones y preguntas abiertas se identifican como pendientes. El RFC-001 ya está consolidado con todas las decisiones vigentes y seis preguntas de producto pendientes; es la especificación del flujo.

## 01 | Alcance del producto

Producto completo y límites de la primera entrega

### Helios completo

Aplicación B2B para instaladoras solares en México, con web y API; una instancia por empresa. CRM de prospectos/clientes, proyectos, recibos privados, ingeniería, proveedores, presupuestos configurables, cotizaciones/PDF, seguimiento, auditoría, usuarios y permisos.

### Papel del Ingeniero de IA

Es el responsable principal del análisis contextual y de escoger los equipos adecuados. Consulta recibo, histórico, tarifa, fases, tensión, demanda y demás campos útiles disponibles; considera ubicación, recurso solar y condiciones conocidas. Identifica faltantes, compara candidatos y justifica decisiones.

### Autoridad de las herramientas

El motor calcula números y cantidades; los validadores deciden compatibilidad. La IA no inventa fórmulas, precios, productos ni especificaciones. Los equipos elegidos deben existir en el catálogo consultado. Dirección y recibo no acreditan por sí solos techo, sombras, estructura o acometida.

### Motor nuevo

Construir desde cero en Helios a partir de docs/syscom-calculator-reverse-engineering.md y su bundle de evidencia. No copiar ni adaptar el PHP de ml-syscom-panel. Las 110 pruebas reportadas del otro proyecto no validan una implementación nueva.

### Syscom inicialmente

Usar su endpoint interno de productos, documentado como accesible sin sesión y sin precios. No requiere credenciales de precios. Contrato y cobertura deben revalidarse al integrar; aún no se reconsultó en esta conversación. Aislar el acceso fuera del motor.

### Fuera de esta entrega

Aislado/híbrido, múltiples proveedores, inventario por usuario, pagos, firma contractual y reserva de stock no están implementados ni comprometidos aquí. El diseño ejecutivo requiere verificaciones físicas y profesionales.

## 02 | Reglas del lead embebido

Correcciones en el formulario; propuesta por correo

### Registro e inicio

Guardar lead y recibo en CRM e iniciar análisis automáticamente. Correo obligatorio en el recorrido futuro. No exigir un botón posterior de generar propuesta ni revisión previa universal por asesor.

### Primera imagen ilegible

Informar el motivo directamente en el mismo formulario. Pedir otra foto o PDF original y permitir una segunda carga en el mismo expediente. No enviar correo para esta corrección normal.

### Segunda imagen ilegible

Cerrar recaptura automática y derivar a asesor. Avisar al lead que su caso requiere revisión. Conservar ambos archivos e historial; no crear otro lead. Son dos cargas en total.

### Ubicación dudosa

Mostrar el mapa en el mismo formulario para confirmar, mover o colocar el punto y corregir la dirección. No enviar correo para esta acción normal. Al confirmar, reanudar el proceso automáticamente.

### Resultado disponible

Si existe configuración completa, compatible y con stock suficiente, generar propuesta. Enviar correo con identidad de la empresa y botón Ver mi propuesta: enlace privado, sin cuenta, para consultar la versión y descargar PDF. No adjuntar PDF inicialmente.

### Fallo técnico bloqueante

Si requiere traspaso humano, informar en el formulario si continúa abierto y por correo que su caso pasó a un asesor. Internamente notificar etapa fallida y diagnóstico. Un fallo técnico no consume cargas ni obliga a fotografiar otra vez.

### No inventar para completar

Distinguir mala calidad de imagen, datos ausentes en recibo legible y error del servicio. No rellenar campos con IA ni publicar cifras/compatibilidades sin sustento. Recuperación tras abandonar formulario sigue pendiente.

## 03 | Ubicación, HSP y cálculo

Semáforo geográfico y catálogo estatal NASA

### Semáforo acordado

Verde: ubicación con señales suficientes, continúa automáticamente. Amarillo: aproximada o ambigua, confirmar en formulario. Rojo: sin resultado utilizable, corregir dirección o colocar punto. Usar texto además de color; no porcentajes inventados por IA.

### Google simulado por ahora

Mientras lectura de archivos sea mock, simular también geocodificación: fixtures verde, amarillo, rojo y fallo técnico. No exigir API key ni facturación. Servicio real y costos se revisan junto con lectura real con IA. No usar fixtures como ubicación verificada.

### Privacidad de ubicación

Usar domicilio del suministro, no domicilio fiscal ni ubicación actual del teléfono. Conservar evidencia y correcciones. La geocodificación externa real y tratamiento de domicilio no quedaron habilitados. Coordenadas y archivos siguen siendo privados.

### NASA por estado

Consultar NASA para construir catálogo HSP de las 32 entidades de México, actualizado periódicamente. No consultar NASA por cada lead. La ubicación permite resolver entidad y aporta contexto al Ingeniero IA. El valor debe identificarse como estatal.

### Método aún pendiente

Definir período climatológico, puntos o malla, agregación geográfica, conversión de unidades, frecuencia y caducidad. Una capital no representa automáticamente al estado. Sin cifras NASA verificadas ni catálogo implementado todavía.

### Versionado del recurso

Guardar HSP, entidad, fuente, metodología y versión usados en la propuesta. Si falla la actualización, conservar última versión válida y avisar internamente. Sin valor válido, bloquear cálculo dependiente; no usar default silencioso.

### Fórmula de interconexión

kWp requeridos = consumo diario / (0.77 × HSP). Históricos de al menos 330 días usan promedio diario en interconexión. Detalles de históricos menores deben seguir el bundle. Redondear a módulos completos puede superar potencia objetivo; cubrir energía no elimina todo el recibo.

## 04 | Equipos y disponibilidad

Reglas distintas por canal y tipo de publicación

### Asesor que carga en CRM

El asesor registra al lead y su recibo; el Ingeniero IA genera su cotización interna. SÍ puede contener equipos bajo pedido, identificados como tales. Esto también aplica a alternativas mostradas en CRM.

### Entrega automática al lead

Solo equipos disponibles en cantidad suficiente para completar la configuración. Stock desconocido o insuficiente no equivale a disponible. Buscar alternativas compatibles antes de derivar el caso.

### Sin combinación disponible

No enviar automáticamente una cotización incompleta o con equipo bajo pedido. Pasar al asesor para revisión y avisar al lead. No ocultar renglones faltantes ni declarar completo el sistema.

### Excepción manual aprobada

El asesor SÍ puede publicar y enviar manualmente una nueva cotización al lead con equipos bajo pedido, claramente identificados. No inventar plazos de entrega. No convertir esa excepción en envío automático.

### Datos del catálogo

Conservar ID de producto, fabricante/modelo, atributos, cantidades, disponibilidad, fecha y fuente. Verificar cobertura por rol: limit=60 no prueba catálogo completo. Falta de atributos puede impedir validar una selección.

### Selección y compatibilidad

El Ingeniero IA compara candidatos utilizando herramientas. El precio no es criterio de ranking mientras no haya datos de costo. La preferencia técnica exacta de mejores equipos aún debe cerrarse. Conservar corrección de módulos por microinversor.

### Stock y versiones

La disponibilidad consultada no reserva existencias. Falta definir vigencia/reconsulta antes de entregar. Cada versión conserva su snapshot; actualizaciones posteriores de catálogo no reescriben una propuesta enviada.

## 05 | Precio, IVA e instalación

Tarifas de venta configuradas por cada empresa

### Tres modalidades aceptadas

Por kWp, por panel y por paquete. Seleccionar según condiciones configuradas por empresa; ejemplo: paquete aplicable y, si existe regla expresa, tarifa por kWp como alternativa. Una base por cálculo, sin duplicar cobros.

### Un solo precio

El usuario pide un importe real o muy cercano a la realidad, no un rango ni una estimación libre. Debe proceder de tarifas vigentes y reglas completas de la empresa, aunque todavía no tenga precios de compra Syscom. Precio de venta no equivale a costo de adquisición.

### Configurador obligatorio

Mostrar: Captura precios sin IVA y sin instalación. El IVA se calcula por separado. La instalación se cotiza por separado. Etiquetar así todas las modalidades; no permitir que el Ingeniero IA omita la exclusión.

### Cotización obligatoria

Desglosar subtotal, IVA y total. Mostrar de forma visible: La instalación no está incluida. Se cotiza por separado. Aplica a CRM, página de propuesta, PDF y cualquier resumen económico del correo. No mostrar instalación como cero ni total instalado.

### IVA y configuración incompleta

Aplicar regla fiscal configurada y guardar base, tasa y resultado. No se acordó una tasa específica en esta conversación. Sin tarifas válidas o regla necesaria, no inventar precio o IVA cero. La entrega pública en ese caso queda por definir.

### Descuentos

Asesor decide descuento por porcentaje o monto fijo, con auditoría. No se acordó aprobación comercial adicional. Validar que no produzca importes inválidos. Base del descuento y posible combinación de ambos tipos siguen pendientes.

### Etapas de entrega

El desarrollo inmediato puede validar equipos sin precios reales de proveedor. El objetivo comercial acordado incorpora importe calculado desde perfiles de empresa. No confundir esa falta de integración con una decisión permanente de cotizar sin precio.

## 06 | Asesor, versiones y seguimiento

Control humano después de la automatización

### Edición de cotización

Asesor puede cambiar equipos/cantidades y aplicar descuentos. Recalcular importes y validar compatibilidad en cada modificación; no ocultar pérdida de cobertura ni incompatibilidades. Conservar actor, fecha y cambios.

### Borrador y envío

Editar borrador sin correos por cada ajuste. Botón Enviar actualización al lead publica nueva versión y notifica por correo. Mantener versión anterior y su enlace; separar publicación de envío para reintentar correo sin duplicar versiones.

### Atención de excepciones

Si existe asesor responsable activo y autorizado, mantenerlo. Si no, enviar a Pendientes de atención. Un asesor pulsa Tomar caso y queda como único responsable; evitar doble toma simultánea.

### Caso sin tomar

Avisar al administrador tras plazo configurable, aún sin duración acordada. No se incluye reparto rotativo en esta primera entrega. Reasignación por asesor inactivo o sin permisos queda por concretar.

### Acciones del lead

Solicitar contacto, pedir cambios y aceptar para continuar con asesor. Registrar versión, fecha y solicitud; notificar responsable o bandeja. Pedir cambios no modifica automáticamente lo publicado. Aceptar no implica pago, firma, reserva ni contratación de instalación.

### Vigencia

Configurable por empresa; existe proposal_validity_days y debe reutilizarse si corresponde. Registrar vencimiento por versión. Expiración de enlace, vigencia comercial y fecha de stock son diferentes.

### Pregunta donde se pausó

Al vencer: propuesta de conservar consulta con aviso Vencida y sustituir Aceptar por Solicitar actualización. EL USUARIO TODAVÍA NO LO CONFIRMÓ. Retomar exactamente esa pregunta antes de fijar comportamiento.

## 07 | Avance y continuidad

Pendientes para la siguiente sesión o agente

### Artefactos existentes

RFC-001: docs/rfcs/001-cotizacion-ia-ubicacion-hsp.md. ADR-012: motor Syscom. ADR-013: ubicación. ADR-014: catálogo HSP estatal. Requisitos e ingeniería solar también recibieron ajustes. MEMORY.md contiene historial acumulativo obligatorio.

### No implementado en esta conversación

Motor nuevo, selección real del Ingeniero IA, adaptador Syscom, catálogo estatal NASA, mock de geocodificación, configurador económico, propuestas versionadas, PDF de producto, entrega por correo, bandeja y acciones del lead. Este PDF es material de junta, no evidencia de esas funciones.

### Pendientes de producto

Seis preguntas en RFC-001: vencimiento/versiones sustituidas; base y combinación de descuentos; alcance de tarifas y falta de precio; prioridades entre equipos; expiración/renovación de enlace; recuperación del formulario y datos faltantes.

### Pendientes técnicos

Verificar bundle para ramas no resumidas; fixtures y pruebas independientes; contrato real/cobertura Syscom; metodología y datos NASA; conectar estados asíncronos sin romper correcciones en formulario; confidencialidad y permisos; idempotencia y trazabilidad.

### Orden sugerido, no aprobado

RFC ya consolidado. Cerrar reglas indispensables; implementar motor puro de interconexión; integrar catálogo; probar recorridos con lector/geocodificación mock; implementar precios configurados y versiones; entrega y seguimiento; luego evaluar lectura IA/geocodificación reales.

### Punto de continuación

RFC-001 reescrito y consolidado: contiene todas las decisiones vigentes, seis preguntas de producto y tareas técnicas separadas. Continuar desde ese RFC y la memoria. No reabrir reglas aceptadas ni interpretar propuestas pendientes como aprobadas.

### Fuentes

Base de cálculo: reverse engineering Syscom y bundle local. Referencias comparativas documentadas en RFC: OpenSolar y Aurora (perfiles), Shopify y Merchant Center (stock), Salesforce y HubSpot (asignación), Google/Mapbox (ubicación), Entrust/Microblink (recaptura). No son tarifas mexicanas ni políticas universales.
