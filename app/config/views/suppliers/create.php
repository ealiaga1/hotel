<h1 class="mb-4">Registrar Nuevo Proveedor</h1>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>
<?php if (isset($success_message)): ?>
    <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Datos del Proveedor</h6>
    </div>
    <div class="card-body">
        <form action="/hotel_completo/public/suppliers/create" method="POST">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="tipo" class="form-label">Tipo <span class="text-danger">*</span></label>
                    <select class="form-select" id="tipo" name="tipo" required>
                        <option value="">Seleccione el tipo</option>
                        <option value="Articulo">Artículo</option>
                        <option value="Servicio">Servicio</option>
                        <option value="Insumos">Insumos</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="modo_pago" class="form-label">Modo de Pago <span class="text-danger">*</span></label>
                    <select class="form-select" id="modo_pago" name="modo_pago" required>
                        <option value="">Seleccione el modo</option>
                        <option value="Contado">Contado</option>
                        <option value="Credito">Crédito</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label for="razon_social" class="form-label">Razón Social/Nombre <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="razon_social" name="razon_social" required>
            </div>
            <div class="mb-3">
                <label for="ruc_dni" class="form-label">RUC/DNI</label>
                <input type="text" class="form-control" id="ruc_dni" name="ruc_dni">
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="departamento" class="form-label">Departamento</label>
                    <select class="form-select" id="departamento" name="departamento">
                        <option value="">Seleccione</option>
                        <?php foreach ($departamentos as $dpto): ?>
                            <option value="<?php echo htmlspecialchars($dpto); ?>"><?php echo htmlspecialchars($dpto); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="provincia" class="form-label">Provincia</label>
                    <input type="text" class="form-control" id="provincia" name="provincia">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="distrito" class="form-label">Distrito</label>
                    <input type="text" class="form-control" id="distrito" name="distrito">
                </div>
            </div>

            <div class="mb-3">
                <label for="direccion" class="form-label">Dirección</label>
                <input type="text" class="form-control" id="direccion" name="direccion">
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="telefono_fijo" class="form-label">Teléfono Fijo</label>
                    <input type="text" class="form-control" id="telefono_fijo" name="telefono_fijo">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="telefono_celular" class="form-label">Celular</label>
                    <input type="text" class="form-control" id="telefono_celular" name="telefono_celular">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="telefono_otro" class="form-label">Otro Teléfono</label>
                    <input type="text" class="form-control" id="telefono_otro" name="telefono_otro">
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="contacto" class="form-label">Persona de Contacto</label>
                    <input type="text" class="form-control" id="contacto" name="contacto">
                </div>
            </div>

            <div class="mb-3">
                <label for="nro_cta_detraccion" class="form-label">Nro. Cta. Detracción</label>
                <input type="text" class="form-control" id="nro_cta_detraccion" name="nro_cta_detraccion">
            </div>

            <div class="mb-3">
                <label for="observaciones" class="form-label">Observaciones</label>
                <textarea class="form-control" id="observaciones" name="observaciones" rows="3"></textarea>
            </div>

            <div class="d-flex justify-content-end">
                <a href="/hotel_completo/public/suppliers" class="btn btn-secondary me-2">Regresar</a>
                <button type="submit" class="btn btn-primary">Guardar Proveedor</button>
            </div>
        </form>
    </div>
</div>
