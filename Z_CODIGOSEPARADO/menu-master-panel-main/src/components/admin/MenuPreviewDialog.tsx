import { Sparkles, MapPin, Clock, Phone, X } from 'lucide-react';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogClose } from '@/components/ui/dialog';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Button } from '@/components/ui/button';
import type { StoreSettings, MenuCategory, MenuItem, Combo } from '@/hooks/useStoreSettings';

interface MenuPreviewDialogProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  settings: StoreSettings | null;
  categories: MenuCategory[];
  menuItems: MenuItem[];
  combos: Combo[];
}

export function MenuPreviewDialog({
  open,
  onOpenChange,
  settings,
  categories,
  menuItems,
  combos,
}: MenuPreviewDialogProps) {
  const highlightedItems = menuItems.filter(item => item.is_highlight);
  const activeCombos = combos.filter(c => c.is_active);
  
  const getItemsByCategory = (categoryId: string) => 
    menuItems.filter(item => item.category_id === categoryId);

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="max-w-lg h-[90vh] p-0 gap-0 overflow-hidden">
        <DialogHeader className="p-4 pb-0 sticky top-0 bg-background z-10">
          <div className="flex items-center justify-between">
            <DialogTitle className="text-lg font-bold">Preview do Cliente</DialogTitle>
            <DialogClose asChild>
              <Button variant="ghost" size="icon" className="h-8 w-8">
                <X className="h-4 w-4" />
              </Button>
            </DialogClose>
          </div>
          
          {/* Store Header Preview */}
          <div className="mt-4 p-4 rounded-lg gradient-primary text-primary-foreground">
            <h2 className="text-xl font-bold">Meu Restaurante</h2>
            <div className="flex flex-wrap items-center gap-3 mt-2 text-sm opacity-90">
              {settings?.is_online ? (
                <span className="flex items-center gap-1">
                  <span className="w-2 h-2 rounded-full bg-green-400 animate-pulse" />
                  Aberto agora
                </span>
              ) : (
                <span className="flex items-center gap-1">
                  <span className="w-2 h-2 rounded-full bg-red-400" />
                  Fechado
                </span>
              )}
              <span className="flex items-center gap-1">
                <Clock className="h-3.5 w-3.5" />
                {settings?.default_prep_time}min
              </span>
              {settings?.whatsapp_number && (
                <span className="flex items-center gap-1">
                  <Phone className="h-3.5 w-3.5" />
                  WhatsApp
                </span>
              )}
            </div>
          </div>
        </DialogHeader>
        
        <ScrollArea className="flex-1 px-4 pb-4">
          <div className="space-y-6 py-4">
            {/* Active Combos */}
            {activeCombos.length > 0 && (
              <section>
                <h3 className="text-sm font-bold uppercase tracking-wider text-primary mb-3 flex items-center gap-1.5">
                  🔥 Promoções
                </h3>
                <div className="space-y-3">
                  {activeCombos.map((combo) => {
                    const discount = combo.original_price
                      ? Math.round((1 - combo.price / combo.original_price) * 100)
                      : null;
                    
                    return (
                      <div
                        key={combo.id}
                        className="p-4 rounded-lg border-2 border-primary/30 bg-primary/5"
                      >
                        <div className="flex items-start justify-between gap-2">
                          <div>
                            <div className="flex items-center gap-2">
                              <h4 className="font-semibold">{combo.name}</h4>
                              {discount && (
                                <span className="px-2 py-0.5 rounded-full bg-primary text-primary-foreground text-xs font-bold">
                                  -{discount}%
                                </span>
                              )}
                            </div>
                            {combo.description && (
                              <p className="text-sm text-muted-foreground mt-1">
                                {combo.description}
                              </p>
                            )}
                          </div>
                          <div className="text-right">
                            <p className="text-lg font-bold text-primary">
                              R$ {combo.price.toFixed(2)}
                            </p>
                            {combo.original_price && (
                              <p className="text-xs text-muted-foreground line-through">
                                R$ {combo.original_price.toFixed(2)}
                              </p>
                            )}
                          </div>
                        </div>
                      </div>
                    );
                  })}
                </div>
              </section>
            )}

            {/* Highlights */}
            {highlightedItems.length > 0 && (
              <section>
                <h3 className="text-sm font-bold uppercase tracking-wider text-primary mb-3 flex items-center gap-1.5">
                  <Sparkles className="h-4 w-4" />
                  Destaques
                </h3>
                <div className="grid grid-cols-1 gap-3">
                  {highlightedItems.map((item) => (
                    <div
                      key={item.id}
                      className="p-4 rounded-lg border border-border bg-card"
                    >
                      <div className="flex items-start justify-between gap-3">
                        <div className="flex-1">
                          <h4 className="font-semibold">{item.name}</h4>
                          {item.description && (
                            <p className="text-sm text-muted-foreground mt-1">
                              {item.description}
                            </p>
                          )}
                        </div>
                        <span className="text-lg font-bold text-foreground">
                          R$ {item.price.toFixed(2)}
                        </span>
                      </div>
                    </div>
                  ))}
                </div>
              </section>
            )}

            {/* Categories */}
            {categories.map((category) => {
              const items = getItemsByCategory(category.id);
              if (items.length === 0) return null;
              
              return (
                <section key={category.id}>
                  <h3 className="text-sm font-bold uppercase tracking-wider text-muted-foreground mb-3">
                    {category.name}
                  </h3>
                  <div className="space-y-2">
                    {items.map((item) => (
                      <div
                        key={item.id}
                        className="p-3 rounded-lg border border-border hover:bg-muted/50 transition-colors"
                      >
                        <div className="flex items-start justify-between gap-2">
                          <div className="flex-1 min-w-0">
                            <div className="flex items-center gap-1.5">
                              <p className="font-medium">{item.name}</p>
                              {item.is_highlight && (
                                <Sparkles className="h-3.5 w-3.5 text-primary" />
                              )}
                            </div>
                            {item.description && (
                              <p className="text-sm text-muted-foreground mt-0.5 line-clamp-2">
                                {item.description}
                              </p>
                            )}
                          </div>
                          <span className="font-semibold whitespace-nowrap">
                            R$ {item.price.toFixed(2)}
                          </span>
                        </div>
                      </div>
                    ))}
                  </div>
                </section>
              );
            })}

            {/* Delivery Info */}
            {settings && settings.min_delivery_value > 0 && (
              <section className="p-4 rounded-lg bg-muted/50 border border-border">
                <div className="flex items-center gap-2 text-sm">
                  <MapPin className="h-4 w-4 text-primary" />
                  <span>Pedido mínimo: <strong>R$ {settings.min_delivery_value.toFixed(2)}</strong></span>
                </div>
              </section>
            )}
          </div>
        </ScrollArea>
      </DialogContent>
    </Dialog>
  );
}
