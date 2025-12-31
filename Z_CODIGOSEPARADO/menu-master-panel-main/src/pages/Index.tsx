import { useState } from 'react';
import { Settings, Tag, Star, Loader2 } from 'lucide-react';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { AdminHeader } from '@/components/admin/AdminHeader';
import { AdminFooter } from '@/components/admin/AdminFooter';
import { GeneralSettingsTab } from '@/components/admin/GeneralSettingsTab';
import { PromotionsTab } from '@/components/admin/PromotionsTab';
import { HighlightsTab } from '@/components/admin/HighlightsTab';
import { MenuPreviewDialog } from '@/components/admin/MenuPreviewDialog';
import { useStoreSettings } from '@/hooks/useStoreSettings';
import { toast } from 'sonner';

const Index = () => {
  const [activeTab, setActiveTab] = useState('general');
  const [previewOpen, setPreviewOpen] = useState(false);
  
  const {
    settings,
    businessHours,
    deliveryZones,
    categories,
    menuItems,
    combos,
    loading,
    saving,
    updateSettings,
    toggleStoreStatus,
    closeStoreEmergency,
    updateBusinessHour,
    addDeliveryZone,
    removeDeliveryZone,
    updateCategoryPriority,
    toggleItemHighlight,
    addCombo,
    toggleComboActive,
    removeCombo,
  } = useStoreSettings();

  const handleSave = () => {
    toast.success('Todas as alterações foram salvas automaticamente!');
  };

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-background">
        <div className="flex flex-col items-center gap-4">
          <Loader2 className="h-8 w-8 animate-spin text-primary" />
          <p className="text-muted-foreground">Carregando painel...</p>
        </div>
      </div>
    );
  }

  if (!settings) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-background">
        <p className="text-destructive">Erro ao carregar configurações</p>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-background pb-24">
      <div className="container max-w-7xl mx-auto px-4 py-6">
        <AdminHeader
          isOnline={settings.is_online}
          onToggleStatus={toggleStoreStatus}
          saving={saving}
        />

        <Tabs value={activeTab} onValueChange={setActiveTab} className="animate-slide-up">
          <TabsList className="w-full justify-start bg-muted/50 p-1 rounded-lg mb-6 flex-wrap h-auto gap-1">
            <TabsTrigger value="general" className="tab-trigger flex items-center gap-2">
              <Settings className="h-4 w-4" />
              <span className="hidden sm:inline">Configurações Gerais</span>
              <span className="sm:hidden">Config</span>
            </TabsTrigger>
            <TabsTrigger value="promotions" className="tab-trigger flex items-center gap-2">
              <Tag className="h-4 w-4" />
              <span className="hidden sm:inline">Promoções & Combos</span>
              <span className="sm:hidden">Promos</span>
            </TabsTrigger>
            <TabsTrigger value="highlights" className="tab-trigger flex items-center gap-2">
              <Star className="h-4 w-4" />
              <span className="hidden sm:inline">Destaques & Prioridades</span>
              <span className="sm:hidden">Destaques</span>
            </TabsTrigger>
          </TabsList>

          <TabsContent value="general" className="mt-0">
            <GeneralSettingsTab
              settings={settings}
              businessHours={businessHours}
              deliveryZones={deliveryZones}
              onUpdateSettings={updateSettings}
              onUpdateBusinessHour={updateBusinessHour}
              onAddDeliveryZone={addDeliveryZone}
              onRemoveDeliveryZone={removeDeliveryZone}
              onEmergencyClose={closeStoreEmergency}
            />
          </TabsContent>

          <TabsContent value="promotions" className="mt-0">
            <PromotionsTab
              combos={combos}
              onAddCombo={addCombo}
              onToggleCombo={toggleComboActive}
              onRemoveCombo={removeCombo}
            />
          </TabsContent>

          <TabsContent value="highlights" className="mt-0">
            <HighlightsTab
              categories={categories}
              menuItems={menuItems}
              onUpdateCategoryPriority={updateCategoryPriority}
              onToggleItemHighlight={toggleItemHighlight}
            />
          </TabsContent>
        </Tabs>
      </div>

      <AdminFooter
        onSave={handleSave}
        onPreview={() => setPreviewOpen(true)}
        saving={saving}
      />

      <MenuPreviewDialog
        open={previewOpen}
        onOpenChange={setPreviewOpen}
        settings={settings}
        categories={categories}
        menuItems={menuItems}
        combos={combos}
      />
    </div>
  );
};

export default Index;
