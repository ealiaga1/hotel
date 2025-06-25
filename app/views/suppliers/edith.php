<h1 class="mb-4">Editar Proveedor: <?php echo htmlspecialchars($supplier['razon_social'] ?? ''); ?></h1>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>
<?php if (isset($success_message)): ?>
    <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>

<?php if (!isset($supplier) || !$supplier): ?>
    <div class="alert alert-warning" role="alert">Proveedor no encontrado.</div>
<?php return; endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Datos del Proveedor</h6>
    </div>
    <div class="card-body">
        <form action="/hotel_completo/public/suppliers/edit/<?php echo htmlspecialchars($supplier['id_proveedor']); ?>" method="POST">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="tipo" class="form-label">Tipo <span class="text-danger">*</span></label>
                    <select class="form-select" id="tipo" name="tipo" required>
                        <option value="">Seleccione el tipo</option>
                        <option value="Articulo" <?php echo ($supplier['tipo'] == 'Articulo') ? 'selected' : ''; ?>>Artículo</option>
                        <option value="Servicio" <?php echo ($supplier['tipo'] == 'Servicio') ? 'selected' : ''; ?>>Servicio</option>
                        <option value="Insumos" <?php echo ($supplier['tipo'] == 'Insumos') ? 'selected' : ''; ?>>Insumos</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="modo_pago" class="form-label">Modo de Pago <span class="text-danger">*</span></label>
                    <select class="form-select" id="modo_pago" name="modo_pago" required>
                        <option value="">Seleccione el modo</option>
                        <option value="Contado" <?php echo ($supplier['modo_pago'] == 'Contado') ? 'selected' : ''; ?>>Contado</option>
                        <option value="Credito" <?php echo ($supplier['modo_pago'] == 'Credito') ? 'selected' : ''; ?>>Crédito</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label for="razon_social" class="form-label">Razón Social/Nombre <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="razon_social" name="razon_social" value="<?php echo htmlspecialchars($supplier['razon_social'] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label for="ruc_dni" class="form-label">RUC/DNI</label>
                <input type="text" class="form-control" id="ruc_dni" name="ruc_dni" value="<?php echo htmlspecialchars($supplier['ruc_dni'] ?? ''); ?>">
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="departamento" class="form-label">Departamento</label>
                    <select class="form-select" id="departamento" name="departamento">
                        <option value="">Seleccione</option>
                        <?php foreach ($departamentos as $dpto): ?>
                            <option value="<?php echo htmlspecialchars($dpto); ?>" <?php echo (($supplier['departamento'] ?? '') == $dpto) ? 'selected' : ''; ?>><?php echo htmlspecialchars($dpto); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="provincia" class="form-label">Provincia</label>
                    <input type="text" class="form-control" id="provincia" name="provincia" value="<?php echo htmlspecialchars($supplier['provincia'] ?? ''); ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="distrito" class="form-label">Distrito</label>
                    <input type="text" class="form-control" id="distrito" name="distrito" value="<?php echo htmlspecialchars($supplier['distrito'] ?? ''); ?>">
                </div>
            </div>

            <div class="mb-3">
                <label for="direccion" class="form-label">Dirección</label>
                <input type="text" class="form-control" id="direccion" name="direccion" value="<?php echo htmlspecialchars($supplier['direccion'] ?? ''); ?>">
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="telefono_fijo" class="form-label">Teléfono Fijo</label>
                    <input type="text" class="form-control" id="telefono_fijo" name="telefono_fijo" value="<?php echo htmlspecialchars($supplier['telefono_fijo'] ?? ''); ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="telefono_celular" class="form-label">Celular</label>
                    <input type="text" class="form-control" id="telefono_celular" name="telefono_celular" value="<?php echo htmlspecialchars($supplier['telefono_celular'] ?? ''); ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="telefono_otro" class="form-label">Otro Teléfono</label>
                    <input type="text" class="form-control" id="telefono_otro" name="telefono_otro" value="<?php echo htmlspecialchars($supplier['telefono_otro'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($supplier['email'] ?? ''); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="contacto" class="form-label">Persona de Contacto</label>
                    <input type="text" class="form-control" id="contacto" name="contacto" value="<?php echo htmlspecialchars($supplier['contacto'] ?? ''); ?>">
                </div>
            </div>

            <div class="mb-3">
                <label for="nro_cta_detraccion" class="form-label">Nro. Cta. Detracción</label>
                <input type="text" class="form-control" id="nro_cta_detraccion" name="nro_cta_detraccion" value="<?php echo htmlspecialchars($supplier['nro_cta_detraccion'] ?? ''); ?>">
            </div>

            <div class="mb-3">
                <label for="observaciones" class="form-label">Observaciones</label>
                <textarea class="form-control" id="observaciones" name="observaciones" rows="3"><?php echo htmlspecialchars($supplier['observaciones'] ?? ''); ?></textarea>
            </div>

            <div class="d-flex justify-content-end">
                <a href="/hotel_completo/public/suppliers" class="btn btn-secondary me-2">Regresar</a>
                <button type="submit" class="btn btn-primary">Actualizar Proveedor</button>
            </div>
        </form>
    </div>
</div>
