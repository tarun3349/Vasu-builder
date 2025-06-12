
import { Building, MapPin, Phone } from "lucide-react";

const Footer = () => {
  return (
    <footer className="bg-primary text-primary-foreground py-12">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
          <div className="col-span-1 md:col-span-2">
            <div className="flex items-center space-x-2 mb-4">
              <Building className="h-8 w-8" />
              <span className="text-2xl font-bold">Vasu Builder</span>
            </div>
            <p className="text-primary-foreground/80 mb-4 max-w-md">
              Premium construction services across Tamil Nadu. Building dreams with quality, 
              precision, and trust since years. Your vision, our expertise.
            </p>
          </div>
          
          <div>
            <h3 className="text-lg font-semibold mb-4">Services</h3>
            <ul className="space-y-2 text-primary-foreground/80">
              <li>Residential Construction</li>
              <li>Commercial Projects</li>
              <li>Renovation & Remodeling</li>
              <li>Interior Design</li>
            </ul>
          </div>
          
          <div>
            <h3 className="text-lg font-semibold mb-4">Contact Info</h3>
            <div className="text-primary-foreground/80 space-y-3">
              <div className="flex items-start space-x-2">
                <Phone className="h-5 w-5 mt-1 flex-shrink-0" />
                <div>
                  <p className="font-medium">+91 98422 10064</p>
                </div>
              </div>
              <div className="flex items-start space-x-2">
                <MapPin className="h-5 w-5 mt-1 flex-shrink-0" />
                <div>
                  <p className="text-sm">20/14, Ayasamy Layout</p>
                  <p className="text-sm">Subbamal Street, Near Om Sakthi Temple</p>
                  <p className="text-sm">Mahalingapuram, Tamil Nadu 642002</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div className="border-t border-primary-foreground/20 mt-8 pt-8 text-center">
          <p className="text-primary-foreground/60">
            © 2024 Vasu Builder. All rights reserved. Premium Construction Services Tamil Nadu.
          </p>
        </div>
      </div>
    </footer>
  );
};

export default Footer;
