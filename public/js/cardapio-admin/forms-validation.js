/**
 * FORMS-VALIDATION.JS - Validação de Formulário
 * 
 * Gerencia validação HTML5 + feedback visual.
 * Parte do módulo CardapioAdmin.
 */

(function (CardapioAdmin) {
    'use strict';

    /**
     * Validação HTML5 + feedback visual
     */
    CardapioAdmin.initValidation = function () {
        const form = document.querySelector('form');
        if (!form) return;

        // Adiciona validação nos campos críticos
        this.addValidation('whatsapp_number', {
            placeholder: '(XX) X XXXX-XXXX'
        });

        this.addValidation('delivery_fee', {
            min: '0',
            title: 'Valor não pode ser negativo'
        });

        this.addValidation('min_order_value', {
            min: '0',
            title: 'Valor não pode ser negativo'
        });

        this.addValidation('delivery_time_min', {
            min: '1',
            max: '180',
            title: 'Entre 1 e 180 minutos'
        });

        this.addValidation('delivery_time_max', {
            min: '1',
            max: '300',
            title: 'Entre 1 e 300 minutos'
        });
    };

    /**
     * Adiciona atributos de validação a um campo
     */
    CardapioAdmin.addValidation = function (fieldId, attrs) {
        const field = document.getElementById(fieldId);
        if (!field) return;

        Object.keys(attrs).forEach(attr => {
            field.setAttribute(attr, attrs[attr]);
        });
    };

    /**
     * Validação antes de submit
     */
    CardapioAdmin.validateForm = function () {
        let isValid = true;
        const errors = [];

        // Validar WhatsApp se habilitado
        const whatsappEnabled = document.getElementById('whatsapp_enabled');
        const whatsappNumber = document.getElementById('whatsapp_number');

        if (whatsappEnabled && whatsappEnabled.checked) {
            const cleanNumber = whatsappNumber.value.replace(/\D/g, '');
            if (!whatsappNumber || cleanNumber.length < 10) {
                errors.push('Número do WhatsApp inválido (mínimo 10 dígitos com DDD)');
                this.highlightError(whatsappNumber);
                isValid = false;
            }
        }

        // Validar PIX se habilitado
        const pixEnabled = document.getElementById('accept_pix');
        const pixKey = document.getElementById('pix_key');

        if (pixEnabled && pixEnabled.checked) {
            if (!pixKey || !pixKey.value.trim()) {
                errors.push('Chave PIX é obrigatória quando PIX está habilitado');
                this.highlightError(pixKey);
                isValid = false;
            }
        }

        // Validar tempo min < max
        const timeMin = document.getElementById('delivery_time_min');
        const timeMax = document.getElementById('delivery_time_max');

        if (timeMin && timeMax) {
            if (parseInt(timeMin.value) > parseInt(timeMax.value)) {
                errors.push('Tempo mínimo não pode ser maior que o máximo');
                this.highlightError(timeMin);
                this.highlightError(timeMax);
                isValid = false;
            }
        }

        if (!isValid) {
            alert('Por favor, corrija os seguintes erros:\n\n• ' + errors.join('\n• '));
        }

        return isValid;
    };

    /**
     * Destaca campo com erro
     */
    CardapioAdmin.highlightError = function (field) {
        if (!field) return;
        field.style.borderColor = '#ef4444';
        field.focus();

        setTimeout(() => {
            field.style.borderColor = '';
        }, 3000);
    };

})(window.CardapioAdmin = window.CardapioAdmin || {});
