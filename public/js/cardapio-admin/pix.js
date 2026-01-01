/**
 * ============================================
 * CARDÁPIO ADMIN - PIX / Máscaras
 * Funções de máscara para PIX, CPF, CNPJ, telefone
 * ============================================
 */

(function (CardapioAdmin) {

    /**
     * [ETAPA 5] Máscara de Telefone BR (9 ou 10 dígitos)
     */
    CardapioAdmin.maskPhone = function (input) {
        let value = input.value.replace(/\D/g, '');

        // Limita a 11 dígitos
        if (value.length > 11) value = value.slice(0, 11);

        // Aplica a máscara visualmente
        if (value.length > 10) {
            value = value.replace(/^(\d{2})(\d{1})(\d{4})(\d{4}).*/, '($1) $2 $3-$4');
        } else if (value.length > 5) {
            value = value.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
        } else if (value.length > 2) {
            value = value.replace(/^(\d{2})(\d{0,5}).*/, '($1) $2');
        } else {
            value = value.replace(/^(\d*)/, '($1');
        }

        input.value = value;
    };

    /**
     * [NOVO] Máscaras Dinâmicas para PIX
     */
    CardapioAdmin.initPixMask = function () {
        const pixKey = document.getElementById('pix_key');
        const pixType = document.getElementById('pix_key_type');

        if (!pixKey || !pixType) return;

        const applyMask = () => {
            const type = pixType.value;
            let value = pixKey.value;

            if (type === 'cpf') value = this.maskCPF(value);
            else if (type === 'cnpj') value = this.maskCNPJ(value);
            else if (type === 'telefone') value = this.maskPhoneValue(value);

            pixKey.value = value;
        };

        pixKey.addEventListener('input', applyMask);

        pixType.addEventListener('change', () => {
            pixKey.value = ''; // Limpa ao mudar o tipo
            pixKey.focus();
        });
    };

    CardapioAdmin.maskCPF = function (v) {
        v = v.replace(/\D/g, "");
        v = v.replace(/(\d{3})(\d)/, "$1.$2");
        v = v.replace(/(\d{3})(\d)/, "$1.$2");
        v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
        return v.substring(0, 14);
    };

    CardapioAdmin.maskCNPJ = function (v) {
        v = v.replace(/\D/g, "");
        v = v.replace(/^(\d{2})(\d)/, "$1.$2");
        v = v.replace(/^(\d{2})\.(\d{3})(\d)/, "$1.$2.$3");
        v = v.replace(/\.(\d{3})(\d)/, ".$1/$2");
        v = v.replace(/(\d{4})(\d)/, "$1-$2");
        return v.substring(0, 18);
    };

    CardapioAdmin.maskPhoneValue = function (v) {
        v = v.replace(/\D/g, "");
        if (v.length > 11) v = v.slice(0, 11);

        if (v.length > 10) {
            return v.replace(/^(\d{2})(\d{1})(\d{4})(\d{4}).*/, '($1) $2 $3-$4');
        } else if (v.length > 5) {
            return v.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
        } else if (v.length > 2) {
            return v.replace(/^(\d{2})(\d{0,5}).*/, '($1) $2');
        } else {
            return v.replace(/^(\d*)/, '($1');
        }
    };

})(window.CardapioAdmin = window.CardapioAdmin || {});
