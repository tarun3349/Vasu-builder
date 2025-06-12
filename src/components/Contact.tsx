
import { Card, CardContent } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Phone, MapPin } from "lucide-react";

const Contact = () => {
  const phoneNumber = "+919842210064";
  
  const handleCall = () => {
    window.open(`tel:${phoneNumber}`);
  };

  return (
    <section id="contact" className="py-20 bg-primary/5">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="text-center mb-16">
          <h2 className="text-3xl md:text-4xl font-bold text-foreground mb-4">
            Ready to Start Your Project?
          </h2>
          <p className="text-xl text-muted-foreground max-w-2xl mx-auto">
            Contact us today and let's discuss how we can build your dream together
          </p>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 max-w-4xl mx-auto">
          <Card className="text-center shadow-xl">
            <CardContent className="p-8">
              <div className="flex justify-center mb-6">
                <div className="bg-primary/10 p-4 rounded-full">
                  <Phone className="h-8 w-8 text-primary" />
                </div>
              </div>
              
              <h3 className="text-2xl font-bold text-foreground mb-2">
                Call Us Now
              </h3>
              
              <p className="text-muted-foreground mb-6">
                Speak directly with our construction experts
              </p>
              
              <div className="mb-6">
                <p className="text-3xl font-bold text-primary mb-2">
                  {phoneNumber}
                </p>
                <p className="text-sm text-muted-foreground">
                  Available 24/7 for consultations
                </p>
              </div>
              
              <Button 
                size="lg" 
                className="w-full text-lg py-3"
                onClick={handleCall}
              >
                <Phone className="mr-2 h-5 w-5" />
                Call Now
              </Button>
            </CardContent>
          </Card>

          <Card className="text-center shadow-xl">
            <CardContent className="p-8">
              <div className="flex justify-center mb-6">
                <div className="bg-primary/10 p-4 rounded-full">
                  <MapPin className="h-8 w-8 text-primary" />
                </div>
              </div>
              
              <h3 className="text-2xl font-bold text-foreground mb-2">
                Visit Our Office
              </h3>
              
              <p className="text-muted-foreground mb-6">
                Come and discuss your project in person
              </p>
              
              <div className="mb-6 text-left">
                <p className="text-foreground font-medium mb-2">
                  20/14, Ayasamy Layout
                </p>
                <p className="text-muted-foreground text-sm mb-1">
                  Subbamal Street
                </p>
                <p className="text-muted-foreground text-sm mb-1">
                  Near Om Sakthi Temple
                </p>
                <p className="text-muted-foreground text-sm mb-1">
                  Mahalingapuram
                </p>
                <p className="text-primary font-semibold">
                  Tamil Nadu 642002
                </p>
              </div>
              
              <Button 
                variant="outline"
                size="lg" 
                className="w-full text-lg py-3"
                onClick={() => window.open("https://maps.google.com/?q=20/14, Ayasamy layout, Subbamal street, near Om sakthi temple, Mahalingapuram, Tamil Nadu 642002")}
              >
                <MapPin className="mr-2 h-5 w-5" />
                Get Directions
              </Button>
            </CardContent>
          </Card>
        </div>

        <div className="text-center mt-12">
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-4xl mx-auto">
            <div>
              <h4 className="font-semibold text-foreground mb-2">Working Hours</h4>
              <p className="text-muted-foreground">Mon - Sat: 8:00 AM - 8:00 PM</p>
              <p className="text-muted-foreground">Sunday: 10:00 AM - 6:00 PM</p>
            </div>
            <div>
              <h4 className="font-semibold text-foreground mb-2">Service Areas</h4>
              <p className="text-muted-foreground">Chennai, Coimbatore, Madurai</p>
              <p className="text-muted-foreground">Trichy, Salem, Erode & More</p>
            </div>
            <div>
              <h4 className="font-semibold text-foreground mb-2">Quick Response</h4>
              <p className="text-muted-foreground">Free consultation within 24 hours</p>
              <p className="text-muted-foreground">Emergency services available</p>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};

export default Contact;
