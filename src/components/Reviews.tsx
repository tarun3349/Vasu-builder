
import { Card, CardContent } from "@/components/ui/card";
import { Star } from "lucide-react";

const Reviews = () => {
  const reviews = [
    {
      name: "Rajesh Kumar",
      location: "Chennai, Tamil Nadu",
      rating: 5,
      review: "Vasu Builder constructed our dream home in Chennai. The quality of work is exceptional and they completed the project on time. Highly recommended!"
    },
    {
      name: "Priya Shankar",
      location: "Coimbatore, Tamil Nadu",
      rating: 5,
      review: "Outstanding service! They built our office complex with great attention to detail. The team is professional and the quality is top-notch."
    },
    {
      name: "Murugan Selvam",
      location: "Madurai, Tamil Nadu",
      rating: 5,
      review: "Best construction company in Tamil Nadu! They renovated our entire house and the transformation is amazing. Very satisfied with their work."
    },
    {
      name: "Lakshmi Devi",
      location: "Trichy, Tamil Nadu",
      rating: 5,
      review: "Vasu Builder team is excellent. They built our villa with premium materials and modern design. The craftsmanship is outstanding!"
    },
    {
      name: "Karthik Raman",
      location: "Salem, Tamil Nadu",
      rating: 5,
      review: "Professional team with great expertise. They completed our commercial project within budget and timeline. Excellent quality work!"
    },
    {
      name: "Deepa Natarajan",
      location: "Erode, Tamil Nadu",
      rating: 5,
      review: "Very impressed with Vasu Builder's work. They built our apartment complex beautifully. Reliable and trustworthy company!"
    }
  ];

  const renderStars = (rating: number) => {
    return Array.from({ length: 5 }, (_, i) => (
      <Star
        key={i}
        className={`h-5 w-5 ${i < rating ? 'text-yellow-400 fill-current' : 'text-gray-300'}`}
      />
    ));
  };

  return (
    <section id="reviews" className="py-20 bg-background">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="text-center mb-16">
          <h2 className="text-3xl md:text-4xl font-bold text-foreground mb-4">
            What Our Clients Say
          </h2>
          <p className="text-xl text-muted-foreground max-w-2xl mx-auto">
            Trusted by hundreds of satisfied customers across Tamil Nadu
          </p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          {reviews.map((review, index) => (
            <Card key={index} className="hover:shadow-lg transition-shadow duration-300">
              <CardContent className="p-6">
                <div className="flex items-center mb-4">
                  {renderStars(review.rating)}
                </div>
                <p className="text-muted-foreground mb-4 italic">
                  "{review.review}"
                </p>
                <div className="border-t pt-4">
                  <p className="font-semibold text-foreground">{review.name}</p>
                  <p className="text-sm text-muted-foreground">{review.location}</p>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>
      </div>
    </section>
  );
};

export default Reviews;
